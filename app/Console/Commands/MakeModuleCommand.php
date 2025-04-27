<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Command to create new modules according to the modular architecture.
 */
class MakeModuleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:module
                            {name? : The name of the module}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new module with the appropriate structure';

    /**
     * Available module types and their folder structures.
     *
     * @var array<string, array<string>>
     */
    /**
     * Available module types and their folder structures.
     * Each type has an array of directories to create and a description.
     *
     * @var array<string, array>
     */
    protected $moduleTypes = [
        'database' => [
            'directories' => ['migrations'],
            'description' => 'Contains only migrations for the database'
        ],
        'entities' => [
            'directories' => ['entities', 'repositories'],
            'description' => 'Contains Eloquent entities and their repositories'
        ],
        'webservice' => [
            'directories' => ['controllers', 'routes', 'middleware', 'requests'],
            'description' => 'Exposes HTTP routes (controllers, routes)'
        ],
        'business' => [
            'directories' => ['services'],
            'description' => 'Pure business logic'
        ],
        'tech' => [
            'directories' => ['services'],
            'description' => 'Technical services, cross-cutting tools'
        ],
        'external' => [
            'directories' => ['services', 'adapters'],
            'description' => 'Integration with external services'
        ],
        'common' => [
            'directories' => ['helpers', 'dtos', 'contracts'],
            'description' => 'Shared tools, DTOs, contracts'
        ]
    ];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->line('');
        $this->info('╔════════════════════════════════════════════╗');
        $this->info('║           MODULE GENERATOR WIZARD          ║');
        $this->info('╚════════════════════════════════════════════╝');
        $this->line('');

        // Prompt for module name if not provided
        $moduleName = $this->argument('name');
        if (!$moduleName) {
            $moduleName = $this->askForModuleName();
        }

        // Ask for module type interactively
        $moduleType = $this->askForModuleType();

        // Show summary and confirm
        $this->line('');
        $this->info('Module Summary:');
        $this->line('  - Name: ' . $moduleName);
        $this->line('  - Type: ' . $moduleType);
        $this->line('  - Directories: ' . implode(', ', $this->moduleTypes[$moduleType]['directories']));
        $this->line('');

        if (!$this->confirm('Do you want to create this module?', true)) {
            $this->warn('Module creation cancelled.');
            return 1;
        }

        // Create module directory
        $moduleDir = base_path("modules/{$moduleName}");

        if (File::exists($moduleDir)) {
            if (!$this->confirm("Module {$moduleName} already exists. Do you want to overwrite it?")) {
                return 1;
            }

            File::deleteDirectory($moduleDir);
        }

        $this->line('');
        $this->comment('Creating module structure...');

        // Create module structure
        File::makeDirectory($moduleDir, 0755, true);

        // Create the directories based on module type
        foreach ($this->moduleTypes[$moduleType]['directories'] as $directory) {
            File::makeDirectory("{$moduleDir}/{$directory}", 0755, true);
            $this->line("  <fg=green>✓</> Created directory: {$directory}");
        }

        // Create ServiceProvider
        $this->createServiceProvider($moduleName, $moduleDir, $moduleType);

        // Create skeleton files based on module type
        $this->createSkeletonFiles($moduleName, $moduleDir, $moduleType);

        $this->line('');
        $this->info("Module {$moduleName} created successfully!");
        return 0;
    }

    /**
     * Ask for module name interactively.
     *
     * @return string
     */
    protected function askForModuleName(): string
    {
        return $this->ask('What is the name of the module?', null, function ($answer) {
            if (empty($answer)) {
                throw new \RuntimeException('Module name cannot be empty.');
            }

            return $answer;
        });
    }

    /**
     * Ask for module type interactively.
     *
     * @return string
     */
    protected function askForModuleType(): string
    {
        $types = array_keys($this->moduleTypes);

        // Préparer les options associatives pour l'affichage
        $typeOptions = [];
        foreach ($types as $type) {
            $typeOptions[$type] = "<fg=green>{$type}</> - {$this->moduleTypes[$type]['description']}";
        }
        
        $this->line('');
        $this->info('Select a module type:');
        $this->line('');

        // Utiliser la méthode choice avec des options numérotées
        return $this->choice(
            'Module type',
            $types,
            'business',
            null,
            false
        );
    }

    /**
     * Create a service provider for the module.
     *
     * @param string $moduleName The name of the module
     * @param string $moduleDir The directory of the module
     * @param string $moduleType The type of module
     * @return void
     */
    protected function createServiceProvider(string $moduleName, string $moduleDir, string $moduleType): void
    {
        $stubContent = $this->getServiceProviderStub($moduleName, $moduleType);
        $providerPath = "{$moduleDir}/{$moduleName}ServiceProvider.php";

        File::put($providerPath, $stubContent);

        $this->line("  <fg=green>✓</> Created service provider: {$moduleName}ServiceProvider.php");
    }

    /**
     * Get service provider stub content based on module type.
     *
     * @param string $moduleName The name of the module
     * @param string $moduleType The type of module
     * @return string
     */
    protected function getServiceProviderStub(string $moduleName, string $moduleType): string
    {
        $namespacedName = "Modules\\{$moduleName}";
        $stub = <<<EOT
<?php

namespace {$namespacedName};

use Illuminate\Support\ServiceProvider;

/**
 * Service provider for the {$moduleName} module.
 *
 * @package {$namespacedName}
 */
class {$moduleName}ServiceProvider extends ServiceProvider
{
    /**
     * Register any module services.
     *
     * @return void
     */
    public function register(): void
    {
        // Register module bindings
    }

    /**
     * Bootstrap any module services.
     *
     * @return void
     */
    public function boot(): void
{
EOT;

        // Add type-specific provider content
        switch ($moduleType) {
            case 'database':
                $stub .= <<<EOT

        // Load migrations
        \$this->loadMigrationsFrom(__DIR__ . '/migrations');
EOT;
                break;
            case 'webservice':
                $stub .= <<<EOT

        // Load routes
        \$this->loadRoutesFrom(__DIR__ . '/routes/api.php');
EOT;
                break;
        }

        // Close the class
        $stub .= <<<EOT

    }
}
EOT;

        return $stub;
    }

    /**
     * Create skeleton files for the module based on its type.
     *
     * @param string $moduleName The name of the module
     * @param string $moduleDir The directory of the module
     * @param string $moduleType The type of module
     * @return void
     */
    protected function createSkeletonFiles(string $moduleName, string $moduleDir, string $moduleType): void
    {
        $this->comment('Creating skeleton files...');

        switch ($moduleType) {
            case 'database':
                $this->createDatabaseFiles($moduleName, $moduleDir);
                break;
            case 'entities':
                $this->createEntityFiles($moduleName, $moduleDir);
                break;
            case 'webservice':
                $this->createWebserviceFiles($moduleName, $moduleDir);
                break;
            case 'business':
                $this->createBusinessFiles($moduleName, $moduleDir);
                break;
            case 'tech':
                $this->createTechFiles($moduleName, $moduleDir);
                break;
            case 'external':
                $this->createExternalFiles($moduleName, $moduleDir);
                break;
            case 'common':
                $this->createCommonFiles($moduleName, $moduleDir);
                break;
        }
    }

    /**
     * Get a singular name from a module name.
     *
     * @param string $moduleName The module name
     * @return string Singular name
     */
    protected function getSingularName(string $moduleName): string
    {
        // Remove common suffixes
        $name = preg_replace('/(?:Manager|Service|Module|ApiClient|Database|s$)/', '', $moduleName);

        // If the name is now empty, use the original
        if (empty($name)) {
            $name = $moduleName;
        }

        return $name;
    }

    /**
     * Get a route name (pluralized, lowercase) from a module name.
     *
     * @param string $moduleName The module name
     * @return string Route name
     */
    protected function getRouteName(string $moduleName): string
    {
        // Get singular first
        $singular = $this->getSingularName($moduleName);

        // Convert camelCase to snake_case
        $snake = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $singular));

        // Pluralize simple English words
        $last = substr($snake, -1);
        if ($last === 'y') {
            return substr($snake, 0, -1) . 'ies';
        } elseif ($last !== 's') {
            return $snake . 's';
        }

        return $snake;
    }

    /**
     * Create database migration sample file.
     *
     * @param string $moduleName The name of the module
     * @param string $moduleDir The directory of the module
     * @return void
     */
    protected function createDatabaseFiles(string $moduleName, string $moduleDir): void
    {
        $timestamp = date('Y_m_d_His');
        $tableName = strtolower(preg_replace('/Database$/', '', $moduleName));
        $tableName = preg_replace('/(?<!^)[A-Z]/', '_$0', $tableName);
        $tableName = strtolower($tableName);

        $migrationPath = "{$moduleDir}/migrations/{$timestamp}_create_{$tableName}_table.php";

        $content = <<<EOT
    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    /**
     * Migration to create the {$tableName} table.
     */
    return new class extends Migration
    {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up(): void
        {
            Schema::create('{$tableName}', function (Blueprint \$table) {
                \$table->id();
                // Define your columns here
                \$table->timestamps();
            });
        }

        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public function down(): void
        {
            Schema::dropIfExists('{$tableName}');
        }
    };
    EOT;

        File::put($migrationPath, $content);
        $this->line("  <fg=green>✓</> Created migration: " . basename($migrationPath));
    }

    /**
     * Create entity model and repository sample files.
     *
     * @param string $moduleName The name of the module
     * @param string $moduleDir The directory of the module
     * @return void
     */
    protected function createEntityFiles(string $moduleName, string $moduleDir): void
    {
        // Create Entity model
        $entityName = $this->getSingularName($moduleName);
        $entityPath = "{$moduleDir}/entities/{$entityName}.php";

        $modelContent = <<<EOT
    <?php

    namespace Modules\\{$moduleName}\\entities;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Factories\HasFactory;

    /**
     * {$entityName} Eloquent model.
     *
     * @package Modules\\{$moduleName}\\entities
     */
    class {$entityName} extends Model
    {
        use HasFactory;

        /**
         * The attributes that are mass assignable.
         *
         * @var array<int, string>
         */
        protected \$fillable = [
            // Define your fillable attributes here
        ];

        /**
         * The attributes that should be cast.
         *
         * @var array<string, string>
         */
        protected \$casts = [
            // Define your castable attributes here
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
    EOT;

        File::put($entityPath, $modelContent);
        $this->line("  <fg=green>✓</> Created entity model: {$entityName}.php");

        // Create Repository
        $repositoryPath = "{$moduleDir}/repositories/{$entityName}Repository.php";

        $repositoryContent = <<<EOT
    <?php

    namespace Modules\\{$moduleName}\\repositories;

    use Modules\\{$moduleName}\\entities\\{$entityName};

    /**
     * Repository for {$entityName} entity.
     *
     * @package Modules\\{$moduleName}\\repositories
     */
    class {$entityName}Repository
    {
        /**
         * Find an entity by its ID.
         *
         * @param int \$id
         * @return {$entityName}|null
         */
        public function findById(int \$id): ?{$entityName}
        {
            return {$entityName}::find(\$id);
        }

        /**
         * Get all entities.
         *
         * @return \Illuminate\Database\Eloquent\Collection<int, {$entityName}>
         */
        public function getAll()
        {
            return {$entityName}::all();
        }

        /**
         * Create a new entity.
         *
         * @param array<string, mixed> \$data
         * @return {$entityName}
         */
        public function create(array \$data): {$entityName}
        {
            return {$entityName}::create(\$data);
        }
    }
    EOT;

        File::put($repositoryPath, $repositoryContent);
        $this->line("  <fg=green>✓</> Created repository: {$entityName}Repository.php");
    }

    /**
     * Create webservice controller and routes sample files.
     *
     * @param string $moduleName The name of the module
     * @param string $moduleDir The directory of the module
     * @return void
     */
    protected function createWebserviceFiles(string $moduleName, string $moduleDir): void
    {
        // Create Controller
        $controllerName = $this->getSingularName($moduleName) . 'Controller';
        $controllerPath = "{$moduleDir}/controllers/{$controllerName}.php";
        $routeName = $this->getRouteName($moduleName);

        $controllerContent = <<<EOT
    <?php

    namespace Modules\\{$moduleName}\\controllers;

    use Illuminate\Http\Request;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Routing\Controller;

    /**
     * Controller for {$moduleName} API endpoints.
     *
     * @package Modules\\{$moduleName}\\controllers
     */
    class {$controllerName} extends Controller
    {
        /**
         * Display a listing of the resource.
         *
         * @return JsonResponse
         */
        public function index(): JsonResponse
        {
            return response()->json([
                'message' => 'List of {$routeName}',
                'data' => [],
            ]);
        }

        /**
         * Store a newly created resource in storage.
         *
         * @param Request \$request
         * @return JsonResponse
         */
        public function store(Request \$request): JsonResponse
        {
            return response()->json([
                'message' => 'Created new resource',
                'data' => \$request->all(),
            ], 201);
        }

        /**
         * Display the specified resource.
         *
         * @param int \$id
         * @return JsonResponse
         */
        public function show(int \$id): JsonResponse
        {
            return response()->json([
                'message' => 'Details for resource ' . \$id,
                'data' => ['id' => \$id],
            ]);
        }
    }
    EOT;

        File::put($controllerPath, $controllerContent);
        $this->line("  <fg=green>✓</> Created controller: {$controllerName}.php");

        // Create Request
        $requestName = 'Store' . $this->getSingularName($moduleName) . 'Request';
        $requestPath = "{$moduleDir}/requests/{$requestName}.php";

        $requestContent = <<<EOT
    <?php

    namespace Modules\\{$moduleName}\\requests;

    use Illuminate\Foundation\Http\FormRequest;

    /**
     * Validation request for storing {$this->getSingularName($moduleName)}.
     *
     * @package Modules\\{$moduleName}\\requests
     */
    class {$requestName} extends FormRequest
    {
        /**
         * Determine if the user is authorized to make this request.
         *
         * @return bool
         */
        public function authorize(): bool
        {
            return true;
        }

        /**
         * Get the validation rules that apply to the request.
         *
         * @return array<string, mixed>
         */
        public function rules(): array
        {
            return [
                // Define your validation rules here
            ];
        }
    }
    EOT;

        File::put($requestPath, $requestContent);
        $this->line("  <fg=green>✓</> Created request: {$requestName}.php");

        // Create Routes
        $routesPath = "{$moduleDir}/routes/api.php";

        $routesContent = <<<EOT
    <?php

    use Illuminate\Support\Facades\Route;
    use Modules\\{$moduleName}\\controllers\\{$controllerName};

    /**
     * API routes for {$moduleName} module.
     */

    Route::prefix('api')->group(function () {
        Route::apiResource('{$routeName}', {$controllerName}::class);
    });
    EOT;

        File::put($routesPath, $routesContent);
        $this->line("  <fg=green>✓</> Created routes: api.php");
    }

    /**
     * Create business service sample files.
     *
     * @param string $moduleName The name of the module
     * @param string $moduleDir The directory of the module
     * @return void
     */
    protected function createBusinessFiles(string $moduleName, string $moduleDir): void
    {
        // Create Service
        $serviceName = $this->getSingularName($moduleName) . "Service";
        $servicePath = "{$moduleDir}/services/{$serviceName}.php";

        $serviceContent = <<<EOT
    <?php

    namespace Modules\\{$moduleName}\\services;

    /**
     * Service for {$moduleName} business logic.
     *
     * @package Modules\\{$moduleName}\\services
     */
    class {$serviceName}
    {
        /**
         * Process business logic.
         *
         * @param array<string, mixed> \$data Input data to process
         * @return array<string, mixed> Processed result
         */
        public function process(array \$data): array
        {
            // Implement business logic here
            return ['processed' => true, 'data' => \$data];
        }
    }
    EOT;

        File::put($servicePath, $serviceContent);
        $this->line("  <fg=green>✓</> Created service: {$serviceName}.php");

        // Create Factory
        $factoryName = $this->getSingularName($moduleName) . "Factory";
        $factoryPath = "{$moduleDir}/factories/{$factoryName}.php";

        $factoryContent = <<<EOT
    <?php

    namespace Modules\\{$moduleName}\\factories;

    /**
     * Factory for creating {$this->getSingularName($moduleName)} objects.
     *
     * @package Modules\\{$moduleName}\\factories
     */
    class {$factoryName}
    {
        /**
         * Create a new instance.
         *
         * @param array<string, mixed> \$attributes
         * @return mixed
         */
        public function create(array \$attributes = [])
        {
            // Implement factory logic here
            return (object) \$attributes;
        }
    }
    EOT;

        File::put($factoryPath, $factoryContent);
        $this->line("  <fg=green>✓</> Created factory: {$factoryName}.php");
    }

    /**
     * Create tech service sample files.
     *
     * @param string $moduleName The name of the module
     * @param string $moduleDir The directory of the module
     * @return void
     */
    protected function createTechFiles(string $moduleName, string $moduleDir): void
    {
        $serviceName = $this->getSingularName($moduleName) . "Service";
        $servicePath = "{$moduleDir}/services/{$serviceName}.php";

        $serviceContent = <<<EOT
    <?php

    namespace Modules\\{$moduleName}\\services;

    /**
     * Technical service for {$moduleName}.
     *
     * @package Modules\\{$moduleName}\\services
     */
    class {$serviceName}
    {
        /**
         * Initialize the service.
         */
        public function __construct()
        {
            // Service initialization
        }

        /**
         * Execute a technical operation.
         *
         * @param array<string, mixed> \$params
         * @return mixed
         */
        public function execute(array \$params)
        {
            // Implement technical logic
            return ['status' => 'success', 'params' => \$params];
        }
    }
    EOT;

        File::put($servicePath, $serviceContent);
        $this->line("  <fg=green>✓</> Created tech service: {$serviceName}.php");
    }

    /**
     * Create external API client sample files.
     *
     * @param string $moduleName The name of the module
     * @param string $moduleDir The directory of the module
     * @return void
     */
    protected function createExternalFiles(string $moduleName, string $moduleDir): void
    {
        // Create API client service
        $serviceName = $this->getSingularName($moduleName) . "Client";
        $servicePath = "{$moduleDir}/services/{$serviceName}.php";

        $serviceContent = <<<EOT
    <?php

    namespace Modules\\{$moduleName}\\services;

    use Illuminate\Http\Client\Response;
    use Illuminate\Support\Facades\Http;

    /**
     * Client for external {$moduleName} API.
     *
     * @package Modules\\{$moduleName}\\services
     */
    class {$serviceName}
    {
        /**
         * @var string Base API URL
         */
        private string \$baseUrl;

        /**
         * Create a new API client.
         *
         * @param string \$baseUrl
         */
        public function __construct(string \$baseUrl = 'https://api.example.com')
        {
            \$this->baseUrl = \$baseUrl;
        }

        /**
         * Get resources from API.
         *
         * @return array<string, mixed>
         */
        public function getResources(): array
        {
            \$response = Http::get(\$this->baseUrl . '/resources');

            return \$this->processResponse(\$response);
        }

        /**
         * Process API response.
         *
         * @param Response \$response
         * @return array<string, mixed>
         */
        private function processResponse(Response \$response): array
        {
            if (\$response->failed()) {
                return ['error' => \$response->status(), 'message' => \$response->body()];
            }

            return \$response->json();
        }
    }
    EOT;

        File::put($servicePath, $serviceContent);
        $this->line("  <fg=green>✓</> Created API client: {$serviceName}.php");

        // Create adapter
        $adapterName = $this->getSingularName($moduleName) . "Adapter";
        $adapterPath = "{$moduleDir}/adapters/{$adapterName}.php";

        $adapterContent = <<<EOT
    <?php

    namespace Modules\\{$moduleName}\\adapters;

    /**
     * Adapter for {$moduleName} external service data.
     *
     * @package Modules\\{$moduleName}\\adapters
     */
    class {$adapterName}
    {
        /**
         * Convert external data to internal format.
         *
         * @param array<string, mixed> \$externalData
         * @return array<string, mixed>
         */
        public function toInternal(array \$externalData): array
        {
            // Transform external data to internal format
            return [
                'id' => \$externalData['external_id'] ?? null,
                'name' => \$externalData['display_name'] ?? null,
                'data' => \$externalData,
            ];
        }

        /**
         * Convert internal data to external format.
         *
         * @param array<string, mixed> \$internalData
         * @return array<string, mixed>
         */
        public function toExternal(array \$internalData): array
        {
            // Transform internal data to external format
            return [
                'external_id' => \$internalData['id'] ?? null,
                'display_name' => \$internalData['name'] ?? null,
            ];
        }
    }
    EOT;

        File::put($adapterPath, $adapterContent);
        $this->line("  <fg=green>✓</> Created adapter: {$adapterName}.php");
    }

    /**
     * Create common files (helpers, DTOs, contracts).
     *
     * @param string $moduleName The name of the module
     * @param string $moduleDir The directory of the module
     * @return void
     */
    protected function createCommonFiles(string $moduleName, string $moduleDir): void
    {
        // Create helper
        $helperName = $this->getSingularName($moduleName) . "Helper";
        $helperPath = "{$moduleDir}/helpers/{$helperName}.php";

        $helperContent = <<<EOT
    <?php

    namespace Modules\\{$moduleName}\\helpers;

    /**
     * Helper functions for {$moduleName}.
     *
     * @package Modules\\{$moduleName}\\helpers
     */
    class {$helperName}
    {
        /**
         * Format data according to standard format.
         *
         * @param mixed \$data
         * @return mixed
         */
        public static function format(\$data)
        {
            // Implement formatting logic
            return \$data;
        }

        /**
         * Validate data against common rules.
         *
         * @param mixed \$data
         * @return bool
         */
        public static function validate(\$data): bool
        {
            // Implement validation logic
            return true;
        }
    }
    EOT;

        File::put($helperPath, $helperContent);
        $this->line("  <fg=green>✓</> Created helper: {$helperName}.php");

        // Create DTO
        $dtoName = $this->getSingularName($moduleName) . "DTO";
        $dtoPath = "{$moduleDir}/dtos/{$dtoName}.php";

        $dtoContent = <<<EOT
    <?php

    namespace Modules\\{$moduleName}\\dtos;

    /**
     * Data Transfer Object for {$this->getSingularName($moduleName)}.
     *
     * @package Modules\\{$moduleName}\\dtos
     */
    class {$dtoName}
    {
        /**
         * @var int|null Identifier
         */
        private ?int \$id;

        /**
         * @var string Name
         */
        private string \$name;

        /**
         * @var array<string, mixed> Additional data
         */
        private array \$data;

        /**
         * Create a new DTO.
         *
         * @param string \$name
         * @param array<string, mixed> \$data
         * @param int|null \$id
         */
        public function __construct(string \$name, array \$data = [], ?int \$id = null)
        {
            \$this->name = \$name;
            \$this->data = \$data;
            \$this->id = \$id;
        }

        /**
         * Get the ID.
         *
         * @return int|null
         */
        public function getId(): ?int
        {
            return \$this->id;
        }

        /**
         * Get the name.
         *
         * @return string
         */
        public function getName(): string
        {
            return \$this->name;
        }

        /**
         * Get additional data.
         *
         * @return array<string, mixed>
         */
        public function getData(): array
        {
            return \$this->data;
        }

        /**
         * Convert DTO to array.
         *
         * @return array<string, mixed>
         */
        public function toArray(): array
        {
            return [
                'id' => \$this->id,
                'name' => \$this->name,
                'data' => \$this->data,
            ];
        }

        /**
         * Create DTO from array.
         *
         * @param array<string, mixed> \$data
         * @return static
         */
        public static function fromArray(array \$data): self
        {
            return new self(
                \$data['name'] ?? '',
                \$data['data'] ?? [],
                \$data['id'] ?? null
            );
        }
    }
    EOT;

        File::put($dtoPath, $dtoContent);
        $this->line("  <fg=green>✓</> Created DTO: {$dtoName}.php");

        // Create contract/interface
        $contractName = $this->getSingularName($moduleName) . "RepositoryInterface";
        $contractPath = "{$moduleDir}/contracts/{$contractName}.php";

        $contractContent = <<<EOT
    <?php

    namespace Modules\\{$moduleName}\\contracts;

    /**
     * Repository interface for {$this->getSingularName($moduleName)}.
     *
     * @package Modules\\{$moduleName}\\contracts
     */
    interface {$contractName}
    {
        /**
         * Find a resource by ID.
         *
         * @param int \$id
         * @return mixed
         */
        public function findById(int \$id);

        /**
         * Get all resources.
         *
         * @return iterable
         */
        public function getAll(): iterable;

        /**
         * Create a new resource.
         *
         * @param array<string, mixed> \$data
         * @return mixed
         */
        public function create(array \$data);

        /**
         * Update a resource.
         *
         * @param int \$id
         * @param array<string, mixed> \$data
         * @return mixed
         */
        public function update(int \$id, array \$data);

        /**
         * Delete a resource.
         *
         * @param int \$id
         * @return bool
         */
        public function delete(int \$id): bool;
    }
    EOT;

        File::put($contractPath, $contractContent);
        $this->line("  <fg=green>✓</> Created contract: {$contractName}.php");
    }
}
