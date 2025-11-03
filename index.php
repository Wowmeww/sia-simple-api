<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Handle preflight (CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . "/db.php";

// Parse the URL path (after /sia2/)
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', trim($uri, '/'));

$resource = $uri[2] ?? null;  // e.g. "products"
$id = $uri[3] ?? null;        // e.g. "1"
$method = $_SERVER['REQUEST_METHOD'];
if (!$resource || $uri[1] !== 'api') {
    echo json_encode(["message" => "Welcome to the SIA2 REST API make sure you're at /sia2/api/products"]);
    exit;
}

// Route based on resource name
switch ($resource) {
    case 'products':
        handleProducts($conn, $method, $id);
        break;

    default:
        http_response_code(404);
        echo json_encode(["error" => "Unknown endpoint"]);
        break;
}

// ------------------ HANDLER FUNCTIONS ------------------

function handleProducts($conn, $method, $id)
{
    switch ($method) {
        case 'GET':
            if ($id) {
                $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
                $stmt->execute([$id]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($product) {
                    echo json_encode($product);
                } else {
                    http_response_code(404);
                    echo json_encode(["error" => "Product not found"]);
                }
            } else {
                $stmt = $conn->query("SELECT * FROM products ORDER BY id DESC");
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents("php://input"), true);
            if (empty($data['name']) || !isset($data['price'])) {
                http_response_code(400);
                echo json_encode(["error" => "Name and price are required"]);
                break;
            }

            $stmt = $conn->prepare("
                INSERT INTO products (name, description, price, stock, image)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['name'],
                $data['description'] ?? '',
                $data['price'],
                $data['stock'] ?? 0,
                $data['image'] ?? ''
            ]);
            echo json_encode(["message" => "Product created successfully"]);
            break;

        case 'PUT':
            if (!$id) {
                http_response_code(400);
                echo json_encode(["error" => "Product ID required"]);
                break;
            }

            $data = json_decode(file_get_contents("php://input"), true);
            $stmt = $conn->prepare("
                UPDATE products
                SET name=?, description=?, price=?, stock=?, image=?
                WHERE id=?
            ");
            $stmt->execute([
                $data['name'],
                $data['description'],
                $data['price'],
                $data['stock'],
                $data['image'],
                $id
            ]);
            echo json_encode(["message" => "Product updated"]);
            break;

        case 'DELETE':
            if (!$id) {
                http_response_code(400);
                echo json_encode(["error" => "Product ID required"]);
                break;
            }

            $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
            $stmt->execute([$id]);
            echo json_encode(["message" => "Product deleted"]);
            break;

        default:
            http_response_code(405);
            echo json_encode(["error" => "Method not allowed"]);
            break;
    }
}
