<?php
header('Content-Type: application/json');
require_once 'ExpenseManager.php';

$manager = new ExpenseManager();
$action = $_GET['action'] ?? '';

try {
    switch($action) {
        case 'get_all':
            echo json_encode([
                'success' => true,
                'data' => $manager->getAllExpenses()
            ]);
            break;
            
        case 'get_categories':
            echo json_encode([
                'success' => true,
                'data' => $manager->getAllCategories()
            ]);
            break;
            
        case 'get_stats':
            $stats = $manager->getStats();
            echo json_encode([
                'success' => true,
                'data' => [
                    'grand_total' => $manager->getGrandTotal(),
                    'paid_total' => $manager->getPaidTotal(),
                    'unpaid_total' => $manager->getUnpaidTotal(),
                    'payment_percentage' => $manager->getPaymentPercentage(),
                    'total_items' => $stats['total_items'],
                    'paid_items' => $stats['paid_items'],
                    'unpaid_items' => $stats['unpaid_items']
                ]
            ]);
            break;
            
        case 'add':
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Gérer la nouvelle catégorie
            if (isset($data['new_category']) && !empty($data['new_category'])) {
                $maxOrder = count($manager->getAllCategories()) + 1;
                $manager->addCategory($data['new_category'], $maxOrder);
                $data['category_id'] = $manager->getLastCategoryId();
                unset($data['new_category']);
            }
            
            $result = $manager->addExpense($data);
            echo json_encode([
                'success' => $result,
                'message' => $result ? 'Dépense ajoutée avec succès' : 'Erreur lors de l\'ajout'
            ]);
            break;
            
        case 'update':
            $id = $_GET['id'] ?? 0;
            $data = json_decode(file_get_contents('php://input'), true);
            $result = $manager->updateExpense($id, $data);
            echo json_encode([
                'success' => $result,
                'message' => $result ? 'Dépense mise à jour avec succès' : 'Erreur lors de la mise à jour'
            ]);
            break;
            
        case 'delete':
            $id = $_GET['id'] ?? 0;
            $result = $manager->deleteExpense($id);
            echo json_encode([
                'success' => $result,
                'message' => $result ? 'Dépense supprimée avec succès' : 'Erreur lors de la suppression'
            ]);
            break;
            
        case 'toggle_paid':
            $id = $_GET['id'] ?? 0;
            $result = $manager->togglePaid($id);
            echo json_encode([
                'success' => $result,
                'message' => $result ? 'Statut mis à jour avec succès' : 'Erreur lors de la mise à jour'
            ]);
            break;
            
        case 'get_by_id':
            $id = $_GET['id'] ?? 0;
            $expense = $manager->getExpenseById($id);
            echo json_encode([
                'success' => $expense !== false,
                'data' => $expense
            ]);
            break;
            
        case 'category_stats':
            $categories = $manager->getAllCategories();
            $stats = [];
            foreach ($categories as $cat) {
                $total = $manager->getCategoryTotal($cat['id']);
                $paid = $manager->getCategoryPaidTotal($cat['id']);
                $stats[] = [
                    'id' => $cat['id'],
                    'name' => $cat['name'],
                    'total' => $total,
                    'paid' => $paid,
                    'remaining' => $total - $paid,
                    'percentage' => $total > 0 ? ($paid / $total) * 100 : 0
                ];
            }
            echo json_encode([
                'success' => true,
                'data' => $stats
            ]);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Action non reconnue'
            ]);
    }
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur: ' . $e->getMessage()
    ]);
}
?>
