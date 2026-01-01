<?php
namespace App\Services;

use App\Config\Database;

class RecommendationService {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getSmartRecommendations($userId, $goal = 'maintain') {
        // 1. Get today's nutrition totals
        $today = date('Y-m-d');
        $stmt = $this->db->conn->prepare("
            SELECT SUM(calories) as cal, SUM(protein) as protein, SUM(carbs) as carbs, SUM(fat) as fat 
            FROM nutrition_logs 
            WHERE user_id = ? AND date = ?
        ");
        $stmt->bind_param("is", $userId, $today);
        $stmt->execute();
        $current = $stmt->get_result()->fetch_assoc();

        // Default values
        $current['cal'] = $current['cal'] ?? 0;
        $current['protein'] = $current['protein'] ?? 0;
        
        $reason = "";
        $mealService = new MealRecommendationService();
        
        // Generate recommendations based on goal and gaps
        if ($goal === 'bulking') {
            $reason = ($current['cal'] < 3000) 
                ? "Target Bulking: Anda butuh surplus kalori! Pilih makanan padat energi."
                : "Target kalori tercapai. Fokus protein untuk otot.";
        } elseif ($goal === 'muscle') {
            $reason = ($current['protein'] < 100)
                ? "Target Muscle: Protein adalah kunci! Tingkatkan asupan protein."
                : "Protein cukup. Jaga karbohidrat untuk energi latihan.";
        } elseif ($goal === 'diet') {
            $reason = ($current['cal'] > 1500) 
                ? "Hati-hati, kalori mendekati batas. Pilih makanan volume besar rendah kalori."
                : "Target Diet: Pilih makanan mengenyangkan tapi rendah kalori.";
        } else {
             $reason = "Jaga keseimbangan nutrisi Anda. Berikut rekomendasi hari ini.";
        }

        // Get actual foods dynamically from DB using MealRecommendationService
        $recommendations = [];
        $meals = ['breakfast', 'lunch', 'dinner', 'snack'];
        $usedIds = [];

        foreach ($meals as $type) {
            $food = $mealService->getSmartFood($type, $goal, $usedIds);
            if ($food) {
                $usedIds[] = $food['id'];
                $recommendations[] = [
                    'name' => $food['name'],
                    'desc' => "Recommended for $type (" . $food['calories'] . " kcal)"
                ];
            }
        }
        
        return [
            'reason' => $reason,
            'foods' => $recommendations
        ];
    }

    // Helper methods removed as they are no longer needed for DB queries
}
