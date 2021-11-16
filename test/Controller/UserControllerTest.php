<?php

namespace ProgrammerZamanNow\Belajar\PHP\MVC\Controller {

    require_once __DIR__ . '/../Helper/helper.php';

    use PHPUnit\Framework\TestCase;
    use ProgrammerZamanNow\Belajar\PHP\MVC\Config\Database;
    use ProgrammerZamanNow\Belajar\PHP\MVC\Domain\Session;
    use ProgrammerZamanNow\Belajar\PHP\MVC\Domain\User;
    use ProgrammerZamanNow\Belajar\PHP\MVC\Model\UserPasswordUpdateRequest;
    use ProgrammerZamanNow\Belajar\PHP\MVC\Repository\SessionRepository;
    use ProgrammerZamanNow\Belajar\PHP\MVC\Repository\UserRepository;
    use ProgrammerZamanNow\Belajar\PHP\MVC\Service\SessionService;

    class UserControllerTest extends TestCase
    {

        private UserController $userController;
        private UserRepository $userRepository;
        private SessionRepository $sessionRepository;

        public function setUp(): void
        {
            $this->userController = new UserController();
            $this->userRepository = new UserRepository(Database::getConnection());
            $this->sessionRepository = new SessionRepository(Database::getConnection());
            $this->sessionRepository->deleteAll();
            $this->userRepository->deleteAll();

            putenv("mode=test");
        }

        public function testRegister()
        {
            $this->userController->register();

            $this->expectOutputRegex("[Register]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[Name]");
            $this->expectOutputRegex("[Passsword]");
            $this->expectOutputRegex("[Register new User]");
        }

        public function testPostRegisterSuccess()
        {
            $_POST['id'] = "bobi";
            $_POST['name'] = "Bobi";
            $_POST['password'] = "rahasia";

            $this->userController->postRegister();

            $this->expectOutputRegex("[Location: /users/login]");
        }

        public function testPostRegisterFailed()
        {
            $_POST['id'] = "";
            $_POST['name'] = "";
            $_POST['password'] = "";

            $this->userController->postRegister();

            $this->expectOutputRegex("[Register]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[Name]");
            $this->expectOutputRegex("[Passsword]");
            $this->expectOutputRegex("[Register new User]");
            $this->expectOutputRegex("[id, name, password cannot blank]");
        }

        public function testPostRegisterDuplicate()
        {
            $user = new User();
            $user->id = "bobi";
            $user->name = "Bobi";
            $user->password = "rahasia";

            $this->userRepository->save($user);

            $_POST['id'] = "bobi";
            $_POST['name'] = "Bobi";
            $_POST['password'] = "rahasia";

            $this->userController->postRegister();

            $this->expectOutputRegex("[Register]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[Name]");
            $this->expectOutputRegex("[Passsword]");
            $this->expectOutputRegex("[Register new User]");
            $this->expectOutputRegex("[User Id already exists]");
        }

        public function testLogin()
        {
            $this->userController->login();

            $this->expectOutputRegex("[Login User]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[Password]");
            $this->expectOutputRegex("[Login]");
        }

        public function testLoginSuccess()
        {
            $user = new User();
            $user->id = "bobi";
            $user->name = "Bobi";
            $user->password = password_hash("rahasia", PASSWORD_BCRYPT);

            $this->userRepository->save($user);

            $_POST['id'] = "bobi";
            $_POST['password'] = "rahasia";

            $this->userController->postLogin();

            $this->expectOutputRegex("[Location: /]");
            $this->expectOutputRegex("[X-PZN-SESSION : ]");
        }

        public function testLoginValidationError()
        {

            $_POST['id'] = "";
            $_POST['password'] = "";

            $this->userController->postLogin();

            $this->expectOutputRegex("[Login User]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[Password]");
            $this->expectOutputRegex("[Login]");
            $this->expectOutputRegex("[id, password cannot blank]");
        }

        public function testLoginUserNotFound()
        {
            $_POST['id'] = "NotFound";
            $_POST['password'] = "notfound";

            $this->userController->postLogin();

            $this->expectOutputRegex("[Login User]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[Password]");
            $this->expectOutputRegex("[Login]");
            $this->expectOutputRegex("[Id or password is wrong]");
        }

        public function testLoginWrongPassword()
        {
            $user = new User();
            $user->id = "bobi";
            $user->name = "Bobi";
            $user->password = password_hash("rahasia", PASSWORD_BCRYPT);

            $this->userRepository->save($user);

            $_POST['id'] = "bobi";
            $_POST['password'] = "salah";

            $this->userController->postLogin();

            $this->expectOutputRegex("[Login User]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[Password]");
            $this->expectOutputRegex("[Login]");
            $this->expectOutputRegex("[Id or password is wrong]");
        }

        public function testLogout()
        {
            $user = new User();
            $user->id = "bobi";
            $user->name = "Bobi";
            $user->password = password_hash("rahasia", PASSWORD_BCRYPT);

            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;

            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $this->userController->logout();

            $this->expectOutputRegex("[Location: /]");
            $this->expectOutputRegex("[X-PZN-SESSION : ]");
        }

        public function testUpdateProfile()
        {
            $user = new User();
            $user->id = "bobi";
            $user->name = "Bobi";
            $user->password = password_hash("rahasia", PASSWORD_BCRYPT);

            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;

            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $this->userController->updateProfile();

            $this->expectOutputRegex("[Profile]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[bobi]");
            $this->expectOutputRegex("[Name]");
            $this->expectOutputRegex("[Bobi]");
        }

        public function testPostUpdateProfileSuccess()
        {
            $user = new User();
            $user->id = "bobi";
            $user->name = "Bobi";
            $user->password = password_hash("rahasia", PASSWORD_BCRYPT);

            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;

            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $_POST['name'] = "Bobi Updated";
            $this->userController->postUpdateProfile();

            $this->expectOutputRegex("[Location: /]");

            $result = $this->userRepository->findById($user->id);
            $this->assertEquals("Bobi Updated", $result->name);
        }

        public function testPostUpdateProfileValidationError()
        {
            $user = new User();
            $user->id = "bobi";
            $user->name = "Bobi";
            $user->password = password_hash("rahasia", PASSWORD_BCRYPT);

            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;

            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $_POST['name'] = "";
            $this->userController->postUpdateProfile();

            $this->expectOutputRegex("[Location: /users/profile]");
            $this->expectOutputRegex("[Profile]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[bobi]");
            $this->expectOutputRegex("[Name]");
            $this->expectOutputRegex("[id, name cannot blank]");
        }

        public function testUpdatePassword()
        {
            $user = new User();
            $user->id = "bobi";
            $user->name = "Bobi";
            $user->password = password_hash("rahasia", PASSWORD_BCRYPT);

            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;

            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $this->userController->updatePassword();

            $this->expectOutputRegex("[Password]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[Old Password]");
            $this->expectOutputRegex("[New Password]");
            $this->expectOutputRegex("[Change Password]");
        }

        public function testPostUpdatePasswordSuccess()
        {
            $user = new User();
            $user->id = "bobi";
            $user->name = "Bobi";
            $user->password = password_hash("rahasia", PASSWORD_BCRYPT);

            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;

            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $_POST['oldPassword'] = "rahasia";
            $_POST['newPassword'] = "new";

            $this->userController->postUpdatePassword();

            $this->expectOutputRegex("[Location: /]");

            $result = $this->userRepository->findById($user->id);
            self::assertTrue(password_verify("new", $result->password));
        }

        public function testPostUpdatePasswordValidationError()
        {
            $user = new User();
            $user->id = "bobi";
            $user->name = "Bobi";
            $user->password = password_hash("rahasia", PASSWORD_BCRYPT);

            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;

            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $_POST['oldPassword'] = "";
            $_POST['newPassword'] = "";

            $this->userController->postUpdatePassword();

            $this->expectOutputRegex("[Password]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[Old Password]");
            $this->expectOutputRegex("[New Password]");
            $this->expectOutputRegex("[Change Password]");
            $this->expectOutputRegex("[id, old password, new password cannot blank]");
        }

        public function testPostUpdatePasswordWrongOldPassword()
        {
            $user = new User();
            $user->id = "bobi";
            $user->name = "Bobi";
            $user->password = password_hash("rahasia", PASSWORD_BCRYPT);

            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;

            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $_POST['oldPassword'] = "salah";
            $_POST['newPassword'] = "new";

            $this->userController->postUpdatePassword();

            $this->expectOutputRegex("[Password]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[Old Password]");
            $this->expectOutputRegex("[New Password]");
            $this->expectOutputRegex("[Change Password]");
            $this->expectOutputRegex("[Old password is wrong]");
        }
    }
}
