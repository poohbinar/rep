<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\Data\CreateDepositData;
use App\Data\DepositHistoryFilterData;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Deposit\AvailableDepositMethodsResponse;
use App\Http\Resources\Deposit\CryptoDepositStateResponse;
use App\Http\Resources\Deposit\DepositFormResponse;
use App\Http\Resources\Deposit\DepositHistoryResponse;
use App\Services\Deposit\DepositService;
use Illuminate\Http\JsonResponse;

class DepositController extends ApiController
{
    public function __construct(private readonly DepositService $depositService) {}

    // STEP 1 — список методов
    public function methods(): JsonResponse
    {
        $user = auth()->user();

        $items = $this->depositService->getAvailableMethods($user);

        $popular = $this->depositService->getUserPopularMethods($user);

        return response()->json(
            new AvailableDepositMethodsResponse($items, $popular),
        );
    }

    public function history(DepositHistoryFilterData $filters): JsonResponse
    {
        $user = auth()->user();

        $deposits = $this->depositService->getDepositHistory($user, $filters);

        return response()->json(
            new DepositHistoryResponse($deposits),
        );
    }

    // STEP 2 — форма и ограничения
    public function methodForm(string $method): JsonResponse
    {
        $user = auth()->user();

        $form = $this->depositService->getMethodForm($user, $method);

        return response()->json(
            new DepositFormResponse($form)
        );
    }

    // STEP 3 — создание депозита + адрес + QR
    public function createCrypto(CreateDepositData $request): JsonResponse
    {
        $user = auth()->user();

        $deposit = $this->depositService->createDeposit(
            user: $user,
            method: $request->paymentMethod,
            fields: $request->fields,
            promoCode: $request->promoCode
        );

        return response()->json(
            new CryptoDepositStateResponse($deposit)
        );
    }

    // STEP 4 — polling статуса
    public function status(string $publicId): JsonResponse
    {
        $user = auth()->user();

        $deposit = $this->depositService->getDepositInfo($user, $publicId);

        return response()->json(
            new CryptoDepositStateResponse($deposit)
        );
    }
}
