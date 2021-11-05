<?php

class Travel
{
    public function fetchData(): array
    {
        $data = file_get_contents(__DIR__ . '/travels.json');

        return json_decode($data, true);
    }

    public function calculateCompanyTravelCost(array &$companies): array
    {
        $travels = $this->fetchData();

        foreach ($companies as &$company) {
            $company['cost'] = 0;

            foreach ($travels as $travel) {
                if ($travel['companyId'] === $company['id']) {
                    $company['cost'] += $travel['price'];
                }
            }
        }

        return $companies;
    }
}

class Company
{
    public function fetchData(): array
    {
        $data = file_get_contents(__DIR__ . '/companies.json');

        return json_decode($data, true);
    }

    public function buildCompaniesTravelCost(array &$companies, string $parentId = '0'): array
    {
        $branches = [];

        foreach ($companies as $key => $company) {
            if ($company['parentId'] === $parentId) {
                $children = self::buildCompaniesTravelCost($companies, $company['id']);

                foreach ($children as $child) {
                    $company['cost'] += $child['cost'];
                }

                $company['children'] = $children;

                $branches[$company['id']] = $company;
                unset($companies[$key]);
            }
        }

        return $branches;
    }
}

class TestScript
{
    public function execute()
    {
        $start = microtime(true);
        $companyService = new Company();
        $travelService = new Travel();
        $companies = $companyService->fetchData();

        $companyWithTravelCost = $travelService->calculateCompanyTravelCost($companies);
        $response = $companyService->buildCompaniesTravelCost($companyWithTravelCost);

        echo json_encode($response);
        echo PHP_EOL;
        echo 'Total time: ' .(microtime(true) - $start);
    }
}

(new TestScript())->execute();
