<?php

require_once '../../config/database.php';
require_once '../../models/Producto.php';

class ProductoResource
{
    private $db;
    private $producto;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->producto = new Producto($this->db);
    }

    private function formatProducto($p)
    {
        return array(
            "id" => (int) $p['id'],
            "sku" => $p['sku'],
            "name" => $p['name'],
            "description" => $p['description'],
            "price" => (float) $p['price'],
            "stock" => (int) $p['stock'],
            "created_at" => $p['created_at'],
            "updated_at" => $p['updated_at']
        );
    }

    // GET /api/v1/productos
    public function index()
    {
        header("Content-Type: application/json");

        $stmt = $this->producto->read();
        $num = $stmt->rowCount();

        $productos_arr = array("records" => array());

        if ($num > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($productos_arr["records"], $this->formatProducto($row));
            }
        }

        http_response_code(200);
        echo json_encode($productos_arr);
    }

    // GET /api/v1/productos/{id}
    public function show($id)
    {
        header("Content-Type: application/json");

        $this->producto->id = $id;

        if ($this->producto->readOne()) {
            $row = array(
                'id' => $this->producto->id,
                'sku' => $this->producto->sku,
                'name' => $this->producto->name,
                'description' => $this->producto->description,
                'price' => $this->producto->price,
                'stock' => $this->producto->stock,
                'created_at' => $this->producto->created_at,
                'updated_at' => $this->producto->updated_at
            );

            http_response_code(200);
            echo json_encode($this->formatProducto($row));
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Producto no encontrado"));
        }
    }

    // POST /api/v1/productos
    public function store()
    {
        header("Content-Type: application/json");

        $data = json_decode(file_get_contents("php://input"));

        if (empty($data->sku) || empty($data->name) || !isset($data->price)) {
            http_response_code(400);
            echo json_encode(array("message" => "Datos incompletos (sku, name y price son requeridos)"));
            return;
        }

        $this->producto->sku = $data->sku;
        $this->producto->name = $data->name;
        $this->producto->description = $data->description ?? null;
        $this->producto->price = $data->price;
        $this->producto->stock = $data->stock ?? 0;

        if ($this->producto->skuExists()) {
            http_response_code(409);
            echo json_encode(array("message" => "El SKU ya está en uso"));
            return;
        }

        try {
            if ($this->producto->create()) {
                http_response_code(201);
                echo json_encode(array(
                    "message" => "Producto creado exitosamente",
                    "id" => $this->producto->id
                ));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo crear el producto"));
            }
        } catch (PDOException $e) {
            http_response_code(409);
            echo json_encode(array("message" => "El SKU ya está en uso"));
        }
    }

    // PUT /api/v1/productos/{id}
    public function update($id)
    {
        header("Content-Type: application/json");

        $data = json_decode(file_get_contents("php://input"));

        $this->producto->id = $id;

        if (empty($data->sku) || empty($data->name) || !isset($data->price)) {
            http_response_code(400);
            echo json_encode(array("message" => "Datos incompletos (sku, name y price son requeridos)"));
            return;
        }

        // Verificar que el producto exista
        if (!$this->producto->readOne()) {
            http_response_code(404);
            echo json_encode(array("message" => "Producto no encontrado"));
            return;
        }

        $this->producto->sku = $data->sku;
        $this->producto->name = $data->name;
        $this->producto->description = $data->description ?? null;
        $this->producto->price = $data->price;
        $this->producto->stock = $data->stock ?? 0;

        if ($this->producto->skuExists()) {
            http_response_code(409);
            echo json_encode(array("message" => "El SKU ya está en uso por otro producto"));
            return;
        }

        try {
            if ($this->producto->update()) {
                http_response_code(200);
                echo json_encode(array("message" => "Producto actualizado exitosamente"));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo actualizar el producto"));
            }
        } catch (PDOException $e) {
            http_response_code(409);
            echo json_encode(array("message" => "El SKU ya está en uso por otro producto"));
        }
    }

    // DELETE /api/v1/productos/{id}
    public function destroy($id)
    {
        header("Content-Type: application/json");

        $this->producto->id = $id;

        if (!$this->producto->readOne()) {
            http_response_code(404);
            echo json_encode(array("message" => "Producto no encontrado"));
            return;
        }

        if ($this->producto->delete()) {
            http_response_code(200);
            echo json_encode(array("message" => "Producto eliminado exitosamente"));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "No se pudo eliminar el producto"));
        }
    }
}
?>