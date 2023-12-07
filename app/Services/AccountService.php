<?php
namespace App\Services;

use App\Models\Account;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AccountService
{

    const ACCOUNT_CACHE_KEY = 'account_%s';
    const ACCOUNT_EXISTS_CACHE_KEY = 'account_id_%s';

    /**
     * @param array $data
     * @throws \Exception
     */
    public function create(array $data): void
    {
        if ($this->getAccountByCache($data['email'])){
            throw new \Exception('has been recorded before');
        }

        $account = new Account();
        $account->fill($data);
        $account->save();

        $this->setAccountExistsCache($account);
    }

    /**
     * @param Account $account
     * @return array
     */
    public function setAccountDataCache(Account $account): array
    {
        $attributes = $account->getAttributes();
        Cache::forever(sprintf(self::ACCOUNT_CACHE_KEY, $account->email), $attributes);

        return $attributes;
    }

    public function setAccountExistsCache(Account $account): void
    {
        Cache::forever(sprintf(self::ACCOUNT_EXISTS_CACHE_KEY, $account->id), $account->email);
    }

    /**
     * @param $accountEmail
     * @return mixed
     */
    public function getAccountByCache($accountEmail)
    {
        $account = Cache::get(sprintf(self::ACCOUNT_CACHE_KEY, $accountEmail));

        if (!$account){
            if ($account = Account::whereEmail($accountEmail)->first()){
                $account = $this->setAccountDataCache($account);
            }
        }

        return $account;
    }

    /**
     * @param $accountId
     * @return array|false|mixed
     */
    public function existsAccountByCache($accountId)
    {
        $accountEmail = Cache::get(sprintf(self::ACCOUNT_EXISTS_CACHE_KEY, $accountId));

        if (!$accountEmail){
            if ($account = Account::whereId($accountId)->first()){
                $this->setAccountExistsCache($account);
                return $this->getAccountByCache($accountEmail);
            }
            return false;
        }

        return $this->getAccountByCache($accountEmail);
    }

    public function getAccountByToken(): array
    {
        $email = Auth::guard('api')->user()->email;
        return $this->getAccountByCache($email);
    }
}
