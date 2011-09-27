<?php
/**
 * Databaza FKS
 *
 * @package     UserMangement
 */

use Nette\Object,
    Nette\Security\IAuthenticator,
    Nette\Security\AuthenticationException,
    Nette\Security\Identity;


/**
 * Authenticator
 *
 * @author     Samuel
 * @package    UserManagement
 */
class Authenticator extends Object implements IAuthenticator
{

    private $_dbConnection;

    public function __construct(\Nette\Database\Connection $dbConnection)
    {
        $this->_dbConnection = $dbConnection;
    }

    private function hash($salt, $password)
    {
        return sha1($salt . $password);
    }

    private function createSalt()
    {
        return \Nette\Utils\Strings::random(15);
    }

    private function getAccountByLogin($login)
    {
        $account = $this->_dbConnection
            ->table('users')
            ->where('login', $login)
            ->fetch();
        if (!$account) {
            throw new AuthenticationException('Nesprávny login');
        }
        return $account;
    }

    public function authenticate(array $credentials)
    {
        list($login, $password) = $credentials;
        $account = $this->getAccountByLogin($login);

        if ($this->hash($account->salt, $password) !== $account->password) {
            throw new AuthenticationException('Nesprávne heslo');
        }

        if ($account->active !== '1') {
            throw new AuthenticationException(
                'Konto doposiaľ nebolo aktivované. Ak ste neobdržali'
              . ' aktivačný email, obráťte sa na nás na otazky@fks.sk');
        }
        return new Identity($account->id);
    }

    public function passwd(array $credentials)
    {
        list($login, $newPassword) = $credentials;
        $account = $this->getAccountByLogin($login);
        $updateData['salt']     = $this->createSalt();
        $updateData['password'] = $this->hash($updateData['salt'], $newPassword);
        $this->_dbConnection->exec(
            'UPDATE users SET ? WHERE login=?',
            $updateData,
            $login
        );
    }

    public function loginExists($login)
    {
        return $this->_dbConnection
            ->table('users')
            ->where('login', $login)
            ->count('*') == 1;
    }
    public function createAccount($id, $credentials, $active = 0)
    {
        list($login, $password) = $credentials;
        $account['id']       = $id;
        $account['login']    = $login;
        $account['salt']     = $this->createSalt();
        $account['password'] = $this->hash($account['salt'], $password);
        $account['active']   = $active;

        if ($this->loginExists($login)) {
            throw new AuthenticationException('Tento login je už obsadený');
        }

        $this->_dbConnection->exec('INSERT INTO users ?', $account);
    }

    public function deleteAccount($id)
    {
        $this->_dbConnection->exec(
            'DELETE FROM users WHERE id=?',
            $id);
    }

    private function setActivation($id, $value)
    {
        $this->_dbConnection->exec(
            'UPDATE users SET ? WHERE id=?',
            array('active' => $value),
            $id);
    }
    public function activateAccount($id)
    {
        $this->setActivation('1');
    }
    public function deactivateAccount($id)
    {
        $this->setActivation('0');
    }


}
