<?php

namespace Modules\UpdateSetsCommand\commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Sets\repositories\SetRepository;

/**
 * Command to update the sets table with data from the Scryfall API.
 *
 * @package Modules\UpdateSetsCommand\commands
 */
class UpdateSetsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:sets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update sets table with data from Scryfall API';

    /**
     * The Scryfall API endpoint for sets.
     *
     * @var string
     */
    private const SCRYFALL_SETS_API_URL = 'https://api.scryfall.com/sets';

    /**
     * Create a new command instance.
     *
     * @param SetRepository $setRepository
     * @return void
     */
    public function __construct(
        private readonly SetRepository $setRepository
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('Fetching sets from Scryfall API...');

        try {
            $sets = $this->fetchSetsFromApi();
            $this->processSets($sets);

            $this->info('Sets updated successfully!');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to update sets: ' . $e->getMessage());
            Log::error('UpdateSetsCommand failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * Fetch sets data from Scryfall API.
     *
     * @return array<int, array<string, mixed>>
     * @throws \Exception If API request fails
     */
    private function fetchSetsFromApi(): array
    {
        $response = Http::get(self::SCRYFALL_SETS_API_URL);

        if (!$response->successful()) {
            throw new \Exception('Failed to fetch sets from Scryfall API: ' . $response->status());
        }

        $data = $response->json();

        if (!isset($data['data']) || !is_array($data['data'])) {
            throw new \Exception('Invalid response format from Scryfall API');
        }

        return $data['data'];
    }

    /**
     * Process and save sets data.
     *
     * @param array<int, array<string, mixed>> $sets
     * @return void
     */
    private function processSets(array $sets): void
    {
        $count = count($sets);
        $this->info("Processing {$count} sets...");

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $updated = 0;
        $created = 0;
        $errors = 0;

        foreach ($sets as $setData) {
            try {
                $this->processSet($setData);
                $created++;
            } catch (\Exception $e) {
                $errors++;
                Log::warning('Failed to process set', [
                    'set' => $setData['code'] ?? 'unknown',
                    'error' => $e->getMessage()
                ]);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Sets processed: {$count}");
        $this->info("Sets created/updated: {$created}");

        if ($errors > 0) {
            $this->warn("Errors encountered: {$errors}");
        }
    }

    /**
     * Process and save a single set.
     *
     * @param array<string, mixed> $setData
     * @return void
     */
    private function processSet(array $setData): void
    {
        // Map Scryfall API fields to our DTO fields
        $mappedData = [
            'uuid' => $setData['id'] ?? '',
            'code' => $setData['code'] ?? '',
            'name' => $setData['name'] ?? '',
            'uri' => $setData['uri'] ?? '',
            'released_at' => $setData['released_at'] ?? '',
            'set_type' => $setData['set_type'] ?? '',
            'card_count' => $setData['card_count'] ?? 0,
            'parent_set_code' => $setData['parent_set_code'] ?? null,
            'digital' => $setData['digital'] ?? false,
            'nonfoil_only' => $setData['nonfoil_only'] ?? false,
            'foil_only' => $setData['foil_only'] ?? false,
            'icon_svg_uri' => $setData['icon_svg_uri'] ?? null
        ];

        // Create or update the set
        $this->setRepository->updateOrCreateFromArray($mappedData);
    }
}
