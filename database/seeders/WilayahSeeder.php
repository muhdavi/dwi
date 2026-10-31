<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WilayahSeeder extends Seeder
{
    private const BATCH_SIZE = 250;

    public function run(): void
    {
        $contents = file_get_contents(database_path('data/wilayah.json'));

        if ($contents === false) {
            throw new \RuntimeException('Unable to read the wilayah dataset.');
        }

        $dataset = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);

        if (! isset($dataset['records']) || ! is_array($dataset['records'])) {
            throw new \UnexpectedValueException('The wilayah dataset has an invalid format.');
        }

        $tables = [
            1 => ['table' => 'provinsis', 'name' => 'provinsi'],
            2 => ['table' => 'kabupatens', 'name' => 'kabupaten', 'parent' => 'provinsi_id'],
            3 => ['table' => 'kecamatans', 'name' => 'kecamatan', 'parent' => 'kabupaten_id'],
            4 => ['table' => 'kelurahans', 'name' => 'desa', 'parent' => 'kecamatan_id'],
        ];

        DB::transaction(function () use ($dataset, $tables): void {
            foreach ($tables as $level => $config) {
                $batch = [];

                foreach ($dataset['records'] as $record) {
                    if (! isset($record['code'], $record['name']) || ! is_string($record['code']) || ! is_string($record['name'])) {
                        throw new \UnexpectedValueException('The wilayah dataset contains an invalid record.');
                    }

                    $parts = explode('.', $record['code']);

                    if (count($parts) !== $level) {
                        continue;
                    }

                    if (count(array_filter($parts, 'ctype_digit')) !== $level) {
                        throw new \UnexpectedValueException("The wilayah dataset contains an invalid code: {$record['code']}.");
                    }

                    $row = [
                        'id' => (int) implode('', $parts),
                        $config['name'] => $record['name'],
                    ];

                    if (isset($config['parent'])) {
                        $row[$config['parent']] = (int) implode('', array_slice($parts, 0, -1));
                    }

                    $batch[] = $row;

                    if (count($batch) >= self::BATCH_SIZE) {
                        DB::table($config['table'])->insert($batch);
                        $batch = [];
                    }
                }

                if ($batch !== []) {
                    DB::table($config['table'])->insert($batch);
                }
            }
        });
    }
}
