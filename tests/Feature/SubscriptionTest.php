<?php

namespace Tests\Feature;

use Faker\Factory;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    /**
     * @var string
     */
    protected string $token;

    /**
     * @var int
     */
    protected int $accountId;


    public function setUp(): void
    {
        parent::setUp();

        $faker = Factory::create();
        $data = [
            "first_name"=> $faker->firstName,
            "last_name" => $faker->lastName,
            'email' => $faker->email,
            'phone_number' => "+905555555555",
            'password' => $faker->password,
            "ip_address"=> $faker->ipv4,
            "country" => "TR",
        ];

        $response = $this->post('http://127.0.0.1:8000/api/register', $data);
        $response->assertStatus(201);
        $data = $response->json();
        $this->token = $data['authorisation']['token'];

        $data = [
            "card_no" => "4111111111111111",
            "card_owner"=> "Test Test",
            "expire_month"=> "12",
            "expire_year"=> "23",
            "cvv"=> "111",
            "package_id"=> "zotlo.premium"
        ];
        $header = ['Authorization' => "Bearer ".$this->token];
        $response = $this->post('http://127.0.0.1:8000/api/subscription/register', $data, $header);
        $response->assertStatus(201);
    }

    public function test_subscription_check(): void
    {
        $header = ['Authorization' => "Bearer ".$this->token];
        $response = $this->get('http://127.0.0.1:8000/api/subscription/check', $header);
        $response->assertStatus(200);
    }

    public function test_subscription_cancel(): void
    {
        $data = [
            "cancellation_reason" => "test 11"
        ];
        $header = ['Authorization' => "Bearer ".$this->token];
        $response = $this->post('http://127.0.0.1:8000/api/subscription/cancel', $data, $header);
        $response->assertStatus(200);
    }


    public function test_account_cards(): void
    {
        $header = ['Authorization' => "Bearer ".$this->token];
        $response = $this->get('http://127.0.0.1:8000/api/account/cards?account_id', $header);
        $response->assertStatus(200);
    }

}
