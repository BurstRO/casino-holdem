<?php

namespace xLink\Tests\Exceptions;

use PHPUnit_Framework_TestCase;
use TypeError;
use xLink\Poker\Game\CashGame;
use Ramsey\Uuid\Uuid;
use xLink\Poker\Game\Chips;
use xLink\Poker\Game\Player;
use xLink\Poker\Game\PlayerCollection;
use xLink\Poker\Client;

class CashGameTest extends PHPUnit_Framework_TestCase
{
    /** @test */
    public function a_cash_game_can_be_setup()
    {
        $id = Uuid::uuid4();
        $name = 'Demo Cash Game';
        $minimumBuyIn = Chips::fromAmount(500);

        $game = CashGame::setUp($id, $name, $minimumBuyIn);
        $this->assertInstanceOf(CashGame::class, $game);
    }

    /**
     * @expectedException TypeError
     * @test
     */
    public function an_exception_is_thrown_when_id_is_not_valid()
    {
        $id = 'abc';
        $name = 'Demo Cash Game';
        $minimumBuyIn = Chips::fromAmount(500);

        $game = CashGame::setUp($id, $name, $minimumBuyIn);
    }

    /** @test */
    public function i_can_see_the_id_of_the_game()
    {
        $id = Uuid::uuid4();
        $name = 'Demo Cash Game';
        $minimumBuyIn = Chips::fromAmount(500);

        $game = CashGame::setUp($id, $name, $minimumBuyIn);
        $this->assertEquals($id, $game->id());
        $this->assertEquals($id, $game->id());
    }

    /** @test */
    public function i_can_see_the_name_of_the_game()
    {
        $id = Uuid::uuid4();
        $name = 'Demo Cash Game';
        $minimumBuyIn = Chips::fromAmount(500);

        $game = CashGame::setUp($id, $name, $minimumBuyIn);
        $this->assertEquals($name, $game->name());
        $this->assertEquals($name, $game->name());
    }

    /** @test */
    public function the_game_should_be_setup_with_no_players_initialy()
    {
        $id = Uuid::uuid4();
        $name = 'Demo Cash Game';
        $minimumBuyIn = Chips::fromAmount(500);

        $game = CashGame::setUp($id, $name, $minimumBuyIn);
        $this->assertEquals(PlayerCollection::make(), $game->players());
        $this->assertEquals(PlayerCollection::make(), $game->players());
        $this->assertEquals(0, $game->players()->count());
    }

    /** @test */
    public function a_client_can_register_to_a_game()
    {
        $id = Uuid::uuid4();
        $name = 'Demo Cash Game';
        $minimumBuyIn = Chips::fromAmount(500);
        $playerName = 'xLink';
        $xLink = Client::register($playerName, $minimumBuyIn);

        $game = CashGame::setUp($id, $name, $minimumBuyIn);
        $game->registerPlayer($xLink);

        /** @var Client $firstPlayer */
        $firstPlayer = $game->players()->first();

        $this->assertEquals(1, $game->players()->count());
        $this->assertEquals($playerName, $firstPlayer->name());
    }

    /** @test */
    public function multiple_clients_can_register_to_a_game()
    {
        $id = Uuid::uuid4();
        $name = 'Demo Cash Game';
        $minimumBuyIn = Chips::fromAmount(500);

        $game = CashGame::setUp($id, $name, $minimumBuyIn);

        $xLink = Client::register('xLink', Chips::fromAmount(1000));
        $Jebus = Client::register('Jebus', Chips::fromAmount(1000));

        $game->registerPlayer($xLink);
        $game->registerPlayer($Jebus);

        $this->assertEquals(Player::fromClient($xLink, $minimumBuyIn), $game->players()->get(0));
        $this->assertEquals(Player::fromClient($Jebus, $minimumBuyIn), $game->players()->get(1));
        $this->assertEquals(2, $game->players()->count());
    }

    /**
     * @expectedException \xLink\Poker\Exceptions\GameException
     * @test
     */
    public function client_cannot_register_to_same_game_twice()
    {
        $id = Uuid::uuid4();
        $name = 'Demo Cash Game';
        $minimumBuyIn = Chips::fromAmount(500);

        $game = CashGame::setUp($id, $name, $minimumBuyIn);

        $xLink = Client::register('xLink', Chips::fromAmount(1000));
        $Jebus = Client::register('Jebus', Chips::fromAmount(1000));

        $game->registerPlayer($xLink);
        $game->registerPlayer($Jebus);
        $game->registerPlayer($xLink);
    }

    /** @test */
    public function a_player_can_buy_into_a_game_with_the_minimum_buy_in()
    {
        $client = Client::register('Bob', Chips::fromAmount(1000));
        $minimumBuyIn = Chips::fromAmount(500);

        $game = CashGame::setUp(Uuid::uuid4(), 'Cash Game', $minimumBuyIn);

        $game->registerPlayer($client, $minimumBuyIn);

        /** @var Player $player */
        $player = $game->players()->first();

        $this->assertInstanceOf(Player::class, $player);
        $this->assertEquals(500, $player->wallet()->amount());
        $this->assertEquals(500, $player->chipStack()->amount());
    }
}