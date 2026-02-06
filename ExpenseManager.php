<?php
require_once 'config.php';

class ExpenseManager {
    private $conn;
    
    public function __construct() {
        $this->conn = getDBConnection();
    }
    
    // Récupérer toutes les dépenses avec leurs catégories
    public function getAllExpenses() {
        $sql = "SELECT e.*, c.name as category_name, c.display_order 
                FROM expenses e 
                JOIN categories c ON e.category_id = c.id 
                ORDER BY c.display_order, e.id";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll();
    }
    
    // Récupérer toutes les catégories
    public function getAllCategories() {
        $sql = "SELECT * FROM categories ORDER BY display_order";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll();
    }
    
    // Récupérer une dépense par ID
    public function getExpenseById($id) {
        $sql = "SELECT * FROM expenses WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    // Ajouter une nouvelle dépense
    public function addExpense($data) {
        $sql = "INSERT INTO expenses (category_id, name, quantity, unit_price, frequency, paid, payment_date, notes) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['category_id'],
            $data['name'],
            $data['quantity'],
            $data['unit_price'],
            $data['frequency'],
            $data['paid'] ? 1 : 0,
            $data['payment_date'] ?? null,
            $data['notes'] ?? null
        ]);
    }
    
    // Mettre à jour une dépense
    public function updateExpense($id, $data) {
        $sql = "UPDATE expenses 
                SET category_id = ?, name = ?, quantity = ?, unit_price = ?, 
                    frequency = ?, paid = ?, payment_date = ?, notes = ? 
                WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['category_id'],
            $data['name'],
            $data['quantity'],
            $data['unit_price'],
            $data['frequency'],
            $data['paid'] ? 1 : 0,
            $data['payment_date'] ?? null,
            $data['notes'] ?? null,
            $id
        ]);
    }
    
    // Supprimer une dépense
    public function deleteExpense($id) {
        $sql = "DELETE FROM expenses WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    // Basculer le statut de paiement
    public function togglePaid($id) {
        $sql = "UPDATE expenses 
                SET paid = NOT paid, 
                    payment_date = CASE WHEN paid = 0 THEN CURDATE() ELSE NULL END 
                WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    // Calculer le montant total d'une dépense
    public function calculateTotal($expense) {
        return $expense['quantity'] * $expense['unit_price'] * $expense['frequency'];
    }
    
    // Obtenir le total général
    public function getGrandTotal() {
        $sql = "SELECT SUM(quantity * unit_price * frequency) as total FROM expenses";
        $stmt = $this->conn->query($sql);
        $result = $stmt->fetch();
        return floatval($result['total'] ?? 0);
    }
    
    // Obtenir le montant payé
    public function getPaidTotal() {
        $sql = "SELECT SUM(quantity * unit_price * frequency) as total 
                FROM expenses WHERE paid = 1";
        $stmt = $this->conn->query($sql);
        $result = $stmt->fetch();
        return floatval($result['total'] ?? 0);
    }
    
    // Obtenir le montant restant
    public function getUnpaidTotal() {
        return $this->getGrandTotal() - $this->getPaidTotal();
    }
    
    // Obtenir le pourcentage de paiement
    public function getPaymentPercentage() {
        $total = $this->getGrandTotal();
        if ($total == 0) return 0;
        return ($this->getPaidTotal() / $total) * 100;
    }
    
    // Obtenir le total par catégorie
    public function getCategoryTotal($categoryId) {
        $sql = "SELECT SUM(quantity * unit_price * frequency) as total 
                FROM expenses WHERE category_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$categoryId]);
        $result = $stmt->fetch();
        return floatval($result['total'] ?? 0);
    }
    
    // Obtenir le montant payé par catégorie
    public function getCategoryPaidTotal($categoryId) {
        $sql = "SELECT SUM(quantity * unit_price * frequency) as total 
                FROM expenses WHERE category_id = ? AND paid = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$categoryId]);
        $result = $stmt->fetch();
        return floatval($result['total'] ?? 0);
    }
    
    // Obtenir les statistiques
    public function getStats() {
        $sql = "SELECT 
                COUNT(*) as total_items,
                SUM(CASE WHEN paid = 1 THEN 1 ELSE 0 END) as paid_items,
                SUM(CASE WHEN paid = 0 THEN 1 ELSE 0 END) as unpaid_items
                FROM expenses";
        $stmt = $this->conn->query($sql);
        return $stmt->fetch();
    }
    
    // Ajouter une nouvelle catégorie
    public function addCategory($name, $order = 0) {
        $sql = "INSERT INTO categories (name, display_order) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$name, $order]);
    }
    
    // Obtenir le dernier ID de catégorie inséré
    public function getLastCategoryId() {
        return $this->conn->lastInsertId();
    }
}
?>
