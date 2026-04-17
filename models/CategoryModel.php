<?php
class CategoryModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Lấy toàn bộ danh sách danh mục (Dùng cho User & Admin)
     */
    public function getAllCategories() {
        $sql = "SELECT * FROM categories ORDER BY name ASC";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Thêm danh mục mới (Admin)
     */
    public function addCategory($name, $icon) {
        $stmt = $this->conn->prepare("INSERT INTO categories (name, icon) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $icon);
        return $stmt->execute();
    }

    /**
     * Cập nhật thông tin danh mục (Admin)
     */
    public function updateCategory($id, $name, $icon) {
        $stmt = $this->conn->prepare("UPDATE categories SET name = ?, icon = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $icon, $id);
        return $stmt->execute();
    }

    /**
     * Xóa danh mục (Admin)
     */
    public function deleteCategory($id) {
        $stmt = $this->conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>