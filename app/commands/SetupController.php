<?php

namespace app\commands;

use app\models\User;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

/** Commands for preparing a fresh installation. */
class SetupController extends Controller
{
    /** Create an operator account without replacing an existing account. */
    public function actionCreateUser($username = 'operator')
    {
        $username = trim((string) $username);

        if ($username === '' || mb_strlen($username) > 50) {
            $this->stderr("نام کاربری باید بین ۱ و ۵۰ نویسه باشد.\n");
            return ExitCode::DATAERR;
        }

        if (User::findByUsername($username) !== null) {
            $this->stderr("این نام کاربری موجود است؛ هیچ تغییری انجام نشد.\n");
            return ExitCode::DATAERR;
        }

        $security = Yii::$app->security;
        $password = $security->generateRandomString(16);

        $user = new User();
        $user->username = $username;
        $user->password_hash = $security->generatePasswordHash($password);
        $user->auth_key = $security->generateRandomString(32);
        $user->role = 'operator';

        if (!$user->save()) {
            $this->stderr(json_encode($user->getErrors(), JSON_UNESCAPED_UNICODE) . "\n");
            return ExitCode::DATAERR;
        }

        $this->stdout("حساب اپراتور ایجاد شد. اطلاعات ورود را نگه دارید:\n");
        $this->stdout("Username: {$username}\nPassword: {$password}\n");

        return ExitCode::OK;
    }
}
