<?php

declare(strict_types=1);

namespace Phosagro\Manager;

final class FilterManager
{
    public function __construct(
        private AgeCategoryManager $ageCategoryManager,
        private CityManager $cityManager,
        private PartnerManager $partnerManager,
        private PhosagroCompanyManager $phosagroCompanyManager
    ) {}

    public function getFilters($partners, $cities, $ages, $companies): array
    {
        $isAll = !$partners && !$cities && !$ages;
        $filters = [];

        if ($ages || $isAll) {
            $ageCategory = $this->ageCategoryManager->findAll();
            foreach ($ageCategory as $age) {
                $filters['ages'][] = [
                    'code' => $age->ageCategoryIdentifier,
                    'name' => $age->name,
                ];
            }
        }

        if ($cities || $isAll) {
            $cityCategory = $this->cityManager->findAll();
            foreach ($cityCategory as $city) {
                $filters['cities'][] = [
                    'id' => $city->code,
                    'name' => $city->name,
                ];
            }
        }

        if ($partners || $isAll) {
            $partnerCategory = $this->partnerManager->findAll();
            foreach ($partnerCategory as $partner) {
                $filters['partners'][] = [
                    'code' => $partner->code,
                    'name' => $partner->name,
                ];
            }
        }

        if ($companies || $isAll) {
            $companies = $this->phosagroCompanyManager->findAll();
            foreach ($companies as $company) {
                $filters['companies'][] = [
                    'code' => $company->bitrixId,
                    'name' => $company->name,
                ];
            }
        }

        return ['filters' => $filters];
    }
}
