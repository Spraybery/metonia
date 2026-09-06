<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class Qs
{
    public static function getSystemName(): string
    {
        return 'Metonia Workshop Management System';
    }

    public static function getAppCode(): string
    {
        return 'METONIA-WMS';
    }

    public static function getSystemLogo(): string
    {
        return asset('assets/images/logo_metonia.png');
    }

    public static function getPanelOptions(): string
    {
        return '';
    }

    public static function format_money($amount, int $decimals = 2, string $currency = 'KES'): string
    {
        return $currency.' '.number_format((float) $amount, $decimals);
    }

    public static function goWithSuccess(string $to, ?string $msg = null): RedirectResponse
    {
        return redirect()->route($to)->with('flash_success', $msg ?? 'Operation completed successfully.');
    }

    public static function goWithDanger(string $to = 'dashboard', ?string $msg = null): RedirectResponse
    {
        return redirect()->route($to)->with('flash_danger', $msg ?? 'An error occurred during operation.');
    }

    public static function json(string $msg, bool $ok = true, array $arr = []): JsonResponse
    {
        return response()->json(array_merge([
            'ok' => $ok,
            'msg' => $msg,
        ], $arr));
    }

    /**
     * The 8 standard sequential vehicle manufacturing & workshop build stages.
     */
    public static function getStages(): array
    {
        return [
            '1. Intake & Diagnosis',
            '2. Structural & Frame',
            '3. Powertrain & Mechanical',
            '4. Electrical & Harness',
            '5. Bodywork & Spray Paint',
            '6. Interior & Glass Fit',
            '7. Quality & Road Test',
            '8. Completed & Dispatched',
        ];
    }

    /**
     * The stage immediately following the given stage in the sequential build
     * pipeline, or null if the vehicle is already on the final stage.
     */
    public static function getNextStage(string $currentStage): ?string
    {
        $stages = self::getStages();
        $index = array_search($currentStage, $stages, true);

        if ($index === false || ! isset($stages[$index + 1])) {
            return null;
        }

        return $stages[$index + 1];
    }

    public static function getMaterialCategories(): array
    {
        return [
            'Consumables',
            'Metals',
            'Aluminium',
            'Bolts & Fasteners',
            'Rubbers',
            'Fibreglass',
            'Worker Safety & PPE',
            'Reflecting & Safety',
        ];
    }

    public static function getMaterialUnits(): array
    {
        return [
            'Pieces',
            'Pairs',
            'Sets',
            'Rolls',
            'Boxes',
            'Liters',
            'Kilograms',
        ];
    }

    public static function getToolCategories(): array
    {
        return [
            'Pneumatic Tools',
            'Torque & Calibration Gauges',
            'Welding & Plasma Cutters',
            'Lifts & Hydraulics',
            'Diagnostic Scanners',
        ];
    }

    public static function getToolStatuses(): array
    {
        return [
            'Available',
            'Checked Out',
        ];
    }

    public static function getUserRoles(): array
    {
        return [
            'Admin',
            'Manager',
            'Storekeeper',
            'Accountant',
        ];
    }
}
