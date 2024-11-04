<?php
namespace App\Swagger;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="Poslar",
 *      description="Pos system api description",
 *      @OA\Contact(
 *          email="benhuang0857@gmail.com"
 *      ),
 *      @OA\License(
 *          name="Apache 2.0",
 *          url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *      )
 * )
 * @OA\PathItem(
 *      path="/"
 * )
 * @OA\Server(
 *      url="http://localhost:8005/api",
 *      description="測試區"
 * )
 * @OA\Server(
 *      url="http://localhost:8005/api",
 *      description="Localhost"
 * )
 * @OA\SecurityScheme(
 *      securityScheme="Authorization",
 *      type="apiKey",
 *      in="header",
 *      name="Authorization"
 * )
 * @OA\Components(
 *     @OA\Response(
 *          response="200",
 *          description="成功",
 *          @OA\JsonContent(
 *              example={
 *                  "status": 200,
 *                  "message": "OK"
 *              }
 *          )
 *     ),
 *     @OA\Response(
 *         response="400",
 *         description="客戶端錯誤",
 *         @OA\JsonContent(
 *             example={
 *                  "status": 400,
 *                  "message": "客戶端錯誤"
 *             }
 *         )
 *     ),
 *     @OA\Response(
 *          response="401",
 *          description="身份驗證失敗",
 *          @OA\JsonContent(
 *              example={
 *                  "status": 401,
 *                  "message": "Unauthorized"
 *              }
 *          )
 *     ),
 *     @OA\Response(
 *          response="404",
 *          description="找不到請求的資源",
 *          @OA\JsonContent(
 *              example={
 *                  "status": 404,
 *                  "message": "Not Found"
 *              }
 *          )
 *     ),
 *     @OA\Response(
 *          response="405",
 *          description="不支援此方法",
 *          @OA\JsonContent(
 *              example={
 *                  "status": 405,
 *                  "message": "Method Not Allowed"
 *              }
 *          )
 *     ),
 *     @OA\Response(
 *          response="500",
 *          description="伺服器發生錯誤",
 *          @OA\JsonContent(
 *              example={
 *                  "status": 500,
 *                  "message": "伺服器發生錯誤"
 *              }
 *          )
 *     ),
 *     @OA\Schema(
 *         schema="Product",
 *         type="object",
 *         required={"id", "name", "price"},
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="Product Name"),
 *         @OA\Property(property="price", type="number", format="float", example=19.99),
 *         @OA\Property(property="optionTypes", type="array",
 *             @OA\Items(ref="#/components/schemas/OptionType")
 *         ),
 *         @OA\Property(property="skus", type="array",
 *             @OA\Items(ref="#/components/schemas/Sku")
 *         ),
 *         @OA\Property(property="categories", type="array",
 *             @OA\Items(ref="#/components/schemas/Category")
 *         )
 *     ),
 *     @OA\Schema(
 *         schema="OptionType",
 *         type="object",
 *         @OA\Property(property="id", type="integer"),
 *         @OA\Property(property="name", type="string")
 *     ),
 *     @OA\Schema(
 *         schema="Sku",
 *         type="object",
 *         @OA\Property(property="id", type="integer"),
 *         @OA\Property(property="code", type="string"),
 *         @OA\Property(property="price", type="number", format="float")
 *     ),
 *     @OA\Schema(
 *         schema="Category",
 *         type="object",
 *         @OA\Property(property="id", type="integer"),
 *         @OA\Property(property="name", type="string")
 *     )
 * )
 */
class ProductsSwagger
{
    /**
     * @OA\Get(
     *     path="/products",
     *     summary="Get all products",
     *     description="Retrieve a list of all products.",
     *     tags={"Products"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="list", type="array",
     *                     @OA\Items(ref="#/components/schemas/Product")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="data", type="string", example="Error message")
     *         )
     *     )
     * )
     */
    public function all() {}

    /**
     * @OA\Get(
     *     path="/products/{id}",
     *     summary="Get a product by ID",
     *     description="Retrieve a product by its ID along with related option types, SKUs, and categories.",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="The ID of the product to retrieve",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="list", type="object", 
     *                     ref="#/components/schemas/Product"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="data", type="string", example="Product not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="data", type="string", example="Error message")
     *         )
     *     )
     * )
     */
    public function show($id) {}

    /**
     * @OA\Post(
     *     path="/products",
     *     summary="Create a new product",
     *     description="Store a new product in the database.",
     *     tags={"Products"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "price", "enable_stock", "stock", "status"},
     *             @OA\Property(property="name", type="string", example="New Product"),
     *             @OA\Property(property="enable_adv_sku", type="boolean", example=true),
     *             @OA\Property(property="sku", type="string", example="SKU123"),
     *             @OA\Property(property="feature_image", type="string", example="image_url.jpg"),
     *             @OA\Property(property="price", type="number", format="float", example=19.99),
     *             @OA\Property(property="enable_stock", type="boolean", example=true),
     *             @OA\Property(property="stock", type="integer", example=100),
     *             @OA\Property(property="description", type="string", example="Product description."),
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="option_types", type="array", 
     *                 @OA\Items(type="integer", example={1, 2})
     *             ),
     *             @OA\Property(property="option_values", type="array", 
     *                 @OA\Items(type="integer", example={3, 4})
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=201),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="message", type="string", example="Success")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=400),
     *             @OA\Property(property="data", type="string", example="Validation error message")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="data", type="string", example="Error message")
     *         )
     *     )
     * )
     */
    
     public function store($request) {}
}
