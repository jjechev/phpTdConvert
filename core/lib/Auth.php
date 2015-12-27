<?php

class Auth
{
    /*
      CREATE TABLE IF NOT EXISTS `users` (
      `id` int(22) NOT NULL AUTO_INCREMENT,
      `name` varchar(99) NOT NULL,
      `username` varchar(99) NOT NULL,
      `password` varchar(99) NOT NULL,
      `email` varchar(255) NOT NULL,
      `confirmed` tinyint(1) NOT NULL,
      `active` tinyint(1) NOT NULL DEFAULT '0',
      `options` int(11) NOT NULL,
      `modulOptions` text NOT NULL,
      `info` text NOT NULL,
      PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=28 ;
     */

    protected $redirectUrl = "/";
    protected $isLogged = false;
    protected $dbTableUsers = 'users';
    protected $template = 'system/loginForm';
    public static $userData = array();

    const CLASSNAME = 'AUTH';

    public function __construct()
    {
        if (Core::$oSession->authUserData !== null)
        {
            self::$userData = Core::$oSession->authUserData;
            $this->isLogged = true;
            Log::log(self::CLASSNAME, Core::$oSession->authUserData);
        }
    }

    public function logout()
    {
        session_destroy();
        Router::redirect($this->redirectUrl);
    }

    public function accessLevel($level = 0)
    {
        if ($level)
            $this->boxLogin();
    }

    public function boxLogin()
    {
        if (InputData::hasPost('redirectUrl'))
            $this->redirectUrl = InputData::getPost('redirectUrl');
        if (InputData::hasGet('url'))
            $this->redirectUrl = InputData::getGet('url');

        if (!$this->isLogged && $_POST)
        {
            $this->login();
        }
        elseif (!$this->isLogged)
        {
            $this->loginForm();
        }
    }

    private function loginForm()
    {
        View::template($this->template, array('redirectUrl' => $this->redirectUrl));
    }

    public function login()
    {
        if ((!$this->isLogged && $_POST))
        {
            if ($_POST)
            {
                $username = Common::normalize("mysql|trim", InputData::getPost('username'));
                $password = Common::normalize("mysql|trim", InputData::getPost('password'));
                $sql = "SELECT * FROM users WHERE password = MD5('$password') and username = '$username' and active = '1';";
                $data = core::$oDb->db_query($sql);
                if ($data && $data[0]['password'] == MD5($password))
                {
                    unset($data[0]['password']);
                    Core::$oSession->authUserData = $data[0];
                    Router::redirect($this->redirectUrl);
                }
                else
                    $this->loginForm();
            }
        }
    }

}
