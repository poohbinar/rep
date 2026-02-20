<?php

use App\Enums\Deposit\AmlRiskLevel;
use App\Enums\Deposit\CryptoAddressStatus;
use App\Enums\Deposit\CryptoTransactionStatus;
use App\Enums\Deposit\DepositStatuses;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // crypto_deposits        — бизнес-намерение
        // payment_targets        — КУДА и КАК платить
        // crypto_addresses       — уникальные адреса (не всегда)
        // buffer_wallets         — общие кошельки
        // crypto_transactions    — on-chain факт
        // crypto_aml_checks      — AML результат

        // ---------------------------------------------------------------------
        // crypto_deposits
        // ---------------------------------------------------------------------
        // Бизнес-намерение пользователя пополнить баланс криптовалютой.
        // Не содержит информации о том, КАК именно производится оплата.
        Schema::create('crypto_deposits', function (Blueprint $table) {
            $table->id();

            // Публичный идентификатор депозита (используется во фронте)
            $table->uuid('public_id')->unique();

            // Пользователь, создавший депозит
            $table->unsignedBigInteger('user_id')->index();

            // Выбранный метод оплаты
            $table->string('payment_method', 32)->index();

            // Внутренний кошелёк пользователя, который пополняется
            $table->unsignedBigInteger('wallet_id')->index();

            // Блокчейн сети: solana, ethereum, bitcoin и т.д.
            $table->string('blockchain', 16);

            // Ожидаемая сумма криптовалюты от пользователя
            $table->azDecimal('expected_crypto_amount')->nullable();

            // Курс криптовалюты к USD на момент зачисления
            $table->azDecimal('usd_rate')->nullable();

            // Итоговая сумма, зачисленная пользователю в USD
            $table->azDecimal('usd_amount')->nullable();

            // Статус депозита:
            $table->unsignedTinyInteger('status')
                ->default(DepositStatuses::DEFAULT_VALUE)
                ->index();

            // Дата создания депозита
            $table->timestamp('created_at')->useCurrent();

            // Дата фактического зачисления средств
            $table->timestamp('credited_at')->nullable();
        });

        // ---------------------------------------------------------------------
        // payment_targets
        // ---------------------------------------------------------------------
        // Инструкция пользователю: КУДА и КАК необходимо отправить средства.
        Schema::create('payment_targets', function (Blueprint $table) {
            $table->id();

            // Связанный криптодепозит
            $table->unsignedBigInteger('crypto_deposit_id')->index();

            // Тип приёма платежа:
            // address — уникальный адрес
            // buffer  — общий кошелёк
            // invoice — внешний платёжный провайдер
            $table->string('type', 16);

            // Блокчейн сети
            $table->string('blockchain', 16);

            // Адрес для оплаты (уникальный или буферный)
            $table->string('address', 128)->nullable();

            // Memo / Tag / Comment для идентификации платежа
            $table->string('memo', 128)->nullable();

            // Время, после которого платёж считается просроченным
            $table->timestamp('expires_at')->nullable();

            // Дата создания payment target
            $table->timestamp('created_at')->useCurrent();
        });

        // ---------------------------------------------------------------------
        // crypto_addresses
        // ---------------------------------------------------------------------
        // Уникальные on-chain адреса, генерируемые из HD-кошелька.
        Schema::create('crypto_addresses', function (Blueprint $table) {
            $table->id();

            // Блокчейн сети
            $table->string('blockchain', 16);

            // Уникальный on-chain адрес
            $table->string('address', 128)->unique();

            // Таргет, к которому привязан адрес
            $table->unsignedBigInteger('payment_target_id')->nullable()->index();

            // Статус адреса
            $table->unsignedTinyInteger('status')
                ->default(CryptoAddressStatus::NEW->value)
                ->index();

            // Дата генерации адреса
            $table->timestamp('created_at')->useCurrent();
        });

        // ---------------------------------------------------------------------
        // buffer_wallets
        // ---------------------------------------------------------------------
        // Общие кошельки для приёма средств (shared wallets).
        Schema::create('buffer_wallets', function (Blueprint $table) {
            $table->id();

            // Блокчейн сети
            $table->string('blockchain', 16);

            // Адрес буферного кошелька
            $table->string('address', 128)->unique();

            // Человекочитаемое название кошелька
            $table->string('label', 64)->nullable();

            // Активен ли кошелёк для приёма средств
            $table->boolean('is_active')->default(true);

            // Дата добавления кошелька
            $table->timestamp('created_at')->useCurrent();
        });

        // ---------------------------------------------------------------------
        // crypto_transactions
        // ---------------------------------------------------------------------
        // Фактические on-chain транзакции, полученные из блокчейна.
        Schema::create('crypto_transactions', function (Blueprint $table) {
            $table->id();

            // Блокчейн сети
            $table->string('blockchain', 16);

            // Хеш (signature) транзакции
            $table->string('tx_hash', 128)->unique();

            // Адрес отправителя
            $table->string('from_address', 128)->index();

            // Адрес получателя
            $table->string('to_address', 128)->index();

            // Memo / Tag транзакции (если есть)
            $table->string('memo', 128)->nullable();

            // Сумма криптовалюты (нормализованная)
            $table->azDecimal('amount');

            // Сумма в минимальных единицах (lamports / wei)
            $table->unsignedBigInteger('amount_raw');

            // Количество подтверждений
            $table->unsignedInteger('confirmations')->default(0);

            // Статус транзакции
            $table->unsignedTinyInteger('status')
                ->default(CryptoTransactionStatus::DETECTED->value)
                ->index();

            // Payment target, по которому сопоставлена транзакция
            $table->unsignedBigInteger('payment_target_id')->index();

            // Дата обнаружения транзакции
            $table->timestamp('detected_at')->useCurrent();
        });

        // ---------------------------------------------------------------------
        // crypto_aml_checks
        // ---------------------------------------------------------------------
        // Результаты AML-проверок on-chain транзакций и адресов.
        Schema::create('crypto_aml_checks', function (Blueprint $table) {
            $table->id();

            // Провайдер AML (amlbot, chainalysis и т.д.)
            $table->string('provider', 32);

            // Блокчейн сети
            $table->string('blockchain', 16);

            // Хеш транзакции
            $table->string('tx_hash', 128)->index();

            // Проверяемый адрес
            $table->string('address', 128)->index();

            // Уровень риска
            $table->unsignedTinyInteger('risk_level')
                ->default(AmlRiskLevel::CLEAN->value)
                ->index();

            // Числовая оценка риска
            $table->unsignedInteger('risk_score')->nullable();

            // Категории риска
            $table->json('categories')->nullable();

            // Полный ответ AML-сервиса
            $table->json('raw_response');

            // Дата выполнения AML-проверки
            $table->timestamp('checked_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crypto_aml_checks');
        Schema::dropIfExists('crypto_transactions');
        Schema::dropIfExists('buffer_wallets');
        Schema::dropIfExists('crypto_addresses');
        Schema::dropIfExists('payment_targets');
        Schema::dropIfExists('crypto_deposits');
    }
};
