<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Crypt\Password;

use ArrayObject;
use Zend\Crypt\Password\Bcrypt;

/**
 * @group      Zend_Crypt
 */
class BcryptTest extends \PHPUnit_Framework_TestCase
{
    /** @var Bcrypt */
    public $bcrypt;
    /** @var string */
    public $salt;
    /** @var string */
    public $bcryptPassword;
    /** @var string */
    public $password;

    public function setUp()
    {
        $this->bcrypt   = new Bcrypt();
        $this->salt     = '1234567890123456';
        $this->password = 'test';
        $this->prefix = '$2y$';

        $this->bcryptPassword = $this->prefix . '10$MTIzNDU2Nzg5MDEyMzQ1Nej0NmcAWSLR.oP7XOR9HD/vjUuOj100y';
    }

    public function testConstructByOptions()
    {
        $options = [
            'cost'       => '15',
            'salt'       => $this->salt
        ];
        $bcrypt  = new Bcrypt($options);
        $this->assertEquals('15', $bcrypt->getCost());
        $this->assertEquals($this->salt, $bcrypt->getSalt());
    }

    /**
     * This test uses ArrayObject to simulate a Zend\Config\Config instance;
     * the class itself only tests for Traversable.
     */
    public function testConstructByConfig()
    {
        $options = [
            'cost'       => '15',
            'salt'       => $this->salt
        ];
        $config  = new ArrayObject($options);
        $bcrypt  = new Bcrypt($config);
        $this->assertEquals('15', $bcrypt->getCost());
        $this->assertEquals($this->salt, $bcrypt->getSalt());
    }

    public function testWrongConstruct()
    {
        $this->setExpectedException(
            'Zend\Crypt\Password\Exception\InvalidArgumentException',
            'The options parameter must be an array or a Traversable'
        );
        $bcrypt = new Bcrypt('test');
    }

    public function testSetCost()
    {
        $this->bcrypt->setCost('16');
        $this->assertEquals('16', $this->bcrypt->getCost());
    }

    public function testSetWrongCost()
    {
        $this->setExpectedException(
            'Zend\Crypt\Password\Exception\InvalidArgumentException',
            'The cost parameter of bcrypt must be in range 04-31'
        );
        $this->bcrypt->setCost('3');
    }

    public function testSetSalt()
    {
        $this->bcrypt->setSalt($this->salt);
        $this->assertEquals($this->salt, $this->bcrypt->getSalt());
    }

    public function testSetSmallSalt()
    {
        $this->setExpectedException(
            'Zend\Crypt\Password\Exception\InvalidArgumentException',
            'The length of the salt must be at least ' . Bcrypt::MIN_SALT_SIZE . ' bytes'
        );
        $this->bcrypt->setSalt('small salt');
    }

    public function testCreateWithRandomSalt()
    {
        $password = $this->bcrypt->create('test');
        $this->assertNotEmpty($password);
        $this->assertEquals(60, strlen($password));
    }

    public function testCreateWithSalt()
    {
        $this->bcrypt->setSalt($this->salt);
        $password = $this->bcrypt->create($this->password);
        $this->assertEquals($password, $this->bcryptPassword);
    }

    public function testVerify()
    {
        $this->assertTrue($this->bcrypt->verify($this->password, $this->bcryptPassword));
        $this->assertFalse($this->bcrypt->verify(substr($this->password, -1), $this->bcryptPassword));
    }

    public function testPasswordWith8bitCharacter()
    {
        $password = 'test' . chr(128);
        $this->bcrypt->setSalt($this->salt);

        $this->assertEquals(
            '$2y$10$MTIzNDU2Nzg5MDEyMzQ1NemFdU/4JOrNpxMym09Mbp0m4hKTgfQo.',
            $this->bcrypt->create($password)
        );
    }
}
