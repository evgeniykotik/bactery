<?php
ini_set("display_errors", 0);
ini_set("display_startup_errors", 0);
error_reporting(E_ALL);

class Errors
{
    static $error = array("errorName" => "Введите правильно имя (только буквы)",
        "errorTelephone" => "Неверно введен номер телефона",
        "errorEmail" => "Введите верно Email",
        "errorTime" => "Положительное число больше ноля");
}

interface BacteryTransform
{
    function transform($amount);
}

class GreenBacteryTransform implements BacteryTransform
{
    public function transform($amount)
    {
        return [Bactery::Red => gmp_mul("$amount", "4"), Bactery::Green => gmp_mul("$amount", "3")];
    }
}

class RedBacteryTransform implements BacteryTransform
{
    public function transform($amount)
    {
        return [Bactery::Red => gmp_mul("$amount", "5"), Bactery::Green => gmp_mul("$amount", "7")];
    }
}

class Bactery
{
    const Red = "RED";
    const Green = "GREEN";
}

class BacteriesProcessor
{

    private $bacteryToTransformation;

    public function __construct()
    {
        $this->bacteryTransformation = [
            Bactery::Red => new RedBacteryTransform(),
            Bactery::Green => new GreenBacteryTransform(),
        ];
    }

    public function process($bacteriesAmount, $time)
    {
        $result = $bacteriesAmount;
        for ($i = 0; $i < $time; $i++) {
            $tempBacteriesAmount = [];
            foreach (array_keys($result) as $key) {
                $transformArray = $this->bacteryTransformation[$key]->transform($result[$key]);
                foreach (array_keys($transformArray) as $transformKey) {
                    if (array_key_exists($transformKey, $tempBacteriesAmount)) {
                        $tempBacteriesAmount[$transformKey] = $transformArray[$transformKey] + $tempBacteriesAmount[$transformKey];
                    } else {
                        $tempBacteriesAmount[$transformKey] = $transformArray[$transformKey];
                    }
                }
            }
            $result = $tempBacteriesAmount;
        }
        return $result;
    }
}

class ValidField
{
    const NAME = "/^[a-z]+$/i";
    const TELEPHONE = "/^(\s*)?(\+)?([- _():=+]?\d[- _():=+]?){8,20}(\s*)?$/";
}

$name = $_POST["name"];
$telephone = $_POST["telephone"];
$email = $_POST["email"];
$time = $_POST["time"];

if (isset($_POST["get"])) {
    if ($name == "" || !preg_match(ValidField::NAME, $name)) {
        $response["errorName"] = Errors::$error["errorName"];
    }
    if (!preg_match(ValidField::TELEPHONE, $telephone)) {
        $response["errorTelephone"] = Errors::$error["errorTelephone"];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response["errorEmail"] = Errors::$error["errorEmail"];
    }
    if ($time < 0) {
        $response["errorTime"] = Errors::$error["errorTime"];
    }
    if (empty($response)) {
        $bacteriesProcessor = new BacteriesProcessor();
        $initialBacteriesAmount = [Bactery::Green => 1, Bactery::Red => 1];
        $result = $bacteriesProcessor->process($initialBacteriesAmount, $time);

    }
}
?>
<!Doctype html>
<meta charset="utf-8">
<head>
    <title>Бактерия</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .count {
            word-break: break-word;
            width: 400px;
        }

        body {
            margin: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: auto;
        }

        form {
            display: flex;
            flex-direction: column;
            width: 400px;
        }

        button {
            margin: 10px 0;
            padding: 5px;
        }

        input {
            margin: 5px 0;
            padding: 5px;
        }

        label {
            margin: 5px;
        }

        p {
            color: crimson;
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>
<body>
<form action="index.php" method="post">
    <label>Имя</label>
    <input type="text" name="name" placeholder="Введите имя" value="<?php echo $name; ?>">
    <p><?php echo $response["errorName"] ?></p>
    <label>Номер телефона</label>
    <input type="tel" name="telephone" placeholder="Введите номер телефона" value="<?php echo $telephone; ?>">
    <p><?php echo $response["errorTelephone"] ?></p>
    <label>Электронная почта</label>
    <input type="email" name="email" placeholder="Введите адрес почты" value="<?php echo $email; ?>">
    <p><?php echo $response["errorEmail"] ?></p>
    <label>Число тактов времени</label>
    <input type="number" name="time" placeholder="Введите такт времени" value="<?php echo $time; ?>">
    <p><?php echo $response["errorTime"] ?></p>
    <button type="submit" name="get" class="sign-btn">Вывести количество бактерий</button>
    <p style="font-size: large; color: green"
       class="count"><?php echo "Количество зеленых бактерий: " . $result[Bactery::Green]; ?></p>
    <p style="font-size: large; color: red"
       class="count"><?php echo "Количество красных бактерий: " . $result[Bactery::Red]; ?></p>
</form>
</body>
</html>

