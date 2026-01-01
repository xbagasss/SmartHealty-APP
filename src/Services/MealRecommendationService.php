<?php
namespace App\Services;

use App\Config\Database;

class MealRecommendationService {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Calculate TDEE for a user
     */
    public function calculateDailyCalories($userId) {
        // Fetch user data
        $stmt = $this->db->conn->prepare("SELECT gender, age, height, activity_level, goal FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        
        // Fetch latest weight
        $wStmt = $this->db->conn->prepare("SELECT weight FROM weight_logs WHERE user_id = ? ORDER BY date DESC, created_at DESC LIMIT 1");
        $wStmt->bind_param("i", $userId);
        $wStmt->execute();
        $wRes = $wStmt->get_result();
        $weight = 60; // Default fallback
        if ($wRes->num_rows > 0) {
            $weight = $wRes->fetch_assoc()['weight'];
        }

        if (!$user) return 2000; // Fallback

        // Mifflin-St Jeor Equation
        // Men: 10W + 6.25H - 5A + 5
        // Women: 10W + 6.25H - 5A - 161
        $bmr = (10 * $weight) + (6.25 * $user['height']) - (5 * $user['age']);
        
        if ($user['gender'] === 'female') {
            $bmr -= 161;
        } else {
            $bmr += 5;
        }

        // Activity Multipliers
        $multipliers = [
            'sedentary' => 1.2,
            'light' => 1.375,
            'moderate' => 1.55,
            'active' => 1.725,
            'athlete' => 1.9
        ];

        $activity = $user['activity_level'] ?? 'moderate';
        $tdee = $bmr * ($multipliers[$activity] ?? 1.55);

        // Adjust based on Goal
        // Diet: -500 (approx 0.5kg loss/week)
        // Muscle: +300-500 (approx 0.25-0.5kg gain/week)
        $goal = $user['goal'] ?? 'maintain';
        if ($goal === 'diet') {
            $tdee -= 500;
        } elseif ($goal === 'muscle') {
            $tdee += 400; // Conservative surplus
        }

        // Safety floors
        if ($tdee < 1200) $tdee = 1200;

        return round($tdee);
    }

    /**
     * Generate plan for a specific date
     */
    /**
     * Generate plan logic (returns array)
     */
    public function getDailyRecommendation($userId) {
        $targetCalories = $this->calculateDailyCalories($userId);
        
        // Fetch user goal for smart filtering
        $stmt = $this->db->conn->prepare("SELECT goal FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $goal = $user['goal'] ?? 'maintain';

        // Calorie Distribution
        $targets = [
            'breakfast' => $targetCalories * 0.25,
            'lunch' => $targetCalories * 0.35,
            'dinner' => $targetCalories * 0.25,
            'snack' => $targetCalories * 0.15
        ];

        $plan = [];
        $usedFoodIds = []; // Prevent duplicates in same day

        foreach ($targets as $mealType => $calTarget) {
            $food = $this->getSmartFood($mealType, $goal, $usedFoodIds);
            
            if ($food) {
                $usedFoodIds[] = $food['id'];
                
                $servings = 1;
                if ($food['calories'] > 0) {
                    $servings = $calTarget / $food['calories'];
                    // Round to nearest 0.5 to make it realistic
                    $servings = round($servings * 2) / 2;
                    if ($servings < 0.5) $servings = 0.5;
                }
                
                $plan[] = [
                    'meal_type' => $mealType,
                    'food_id' => $food['id'],
                    'food_name' => $food['name'],
                    'calories' => $food['calories'], // per serving
                    'servings' => $servings,
                    'total_calories' => $food['calories'] * $servings,
                    'notes' => "Rekomendasi (" . round($calTarget) . " kcal)"
                ];
            }
        }
        return $plan;
    }

    /**
     * Generate and Save plan for a specific date
     */
    public function generateDailyPlan($userId, $date) {
        $planItems = $this->getDailyRecommendation($userId);
        
        // Clear existing plan
        $del = $this->db->conn->prepare("DELETE FROM meal_plans WHERE user_id = ? AND plan_date = ?");
        $del->bind_param("is", $userId, $date);
        $del->execute();

        // Save new plan
        $ins = $this->db->conn->prepare("INSERT INTO meal_plans (user_id, plan_date, meal_type, food_id, servings, notes) VALUES (?, ?, ?, ?, ?, ?)");
        
        foreach ($planItems as $item) {
            $ins->bind_param("issids", $userId, $date, $item['meal_type'], $item['food_id'], $item['servings'], $item['notes']);
            $ins->execute();
        }

        return true;
    }

    public function getSmartFood($mealType, $goal, $excludeIds = []) {
        $category = 'main';
        if ($mealType === 'breakfast') $category = 'breakfast';
        if ($mealType === 'snack') $category = 'snack';
        
        // Base Query
        $sql = "SELECT id, name, calories, protein, carbs, fat FROM foods WHERE category = ?";
        
        // Filter Exclusions
        if (!empty($excludeIds)) {
            $in = str_repeat('?,', count($excludeIds) - 1) . '?';
            $sql .= " AND id NOT IN ($in)";
        }
        
        // Smart ORDER BY based on Goal
        if ($goal === 'diet') {
            // Diet: Maximize Protein per Calorie (Satiety & Muscle Retention)
            // Score = (Protein * 10) - (Calories * 0.1)
            $sql .= " ORDER BY (protein / GREATEST(calories, 1)) DESC, RAND()";
        } elseif ($goal === 'muscle') {
            // Muscle: High Protein & Moderate Carbs
            // Score = Protein + (carbs * 0.5)
            $sql .= " ORDER BY (protein + (carbs * 0.5)) DESC, RAND()";
        } elseif ($goal === 'bulking') {
            // Bulking: Maximize Calorie Density (Easy to eat surplus)
            // Score = Calories
            $sql .= " ORDER BY calories DESC, RAND()";
        } else {
            // Maintain: Balanced approach, slightly random but favoring good macros
            $sql .= " ORDER BY RAND()";
        }
        
        $sql .= " LIMIT 5"; // Get top 5 candidates

        $stmt = $this->db->conn->prepare($sql);
        
        // Bind Params dynamically
        $types = "s";
        $params = [$category];
        foreach ($excludeIds as $id) {
            $types .= "i";
            $params[] = $id;
        }
        
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();

        // Pick one random from the top 5 candidates to add variety
        // otherwise it always picks the absolute #1 best food
        $candidates = [];
        while ($row = $res->fetch_assoc()) {
            $candidates[] = $row;
        }
        
        if (!empty($candidates)) {
            return $candidates[array_rand($candidates)];
        }

        // Fallback: If strict filtering returns nothing (e.g. empty category), relax constraints
        // Try any food from category ignoring exclusions if needed (though unlikely)
        return $this->db->conn->query("SELECT id, name, calories, protein, carbs, fat FROM foods WHERE category = '$category' ORDER BY RAND() LIMIT 1")->fetch_assoc();
    }
}
