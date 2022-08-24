<?php
?>
<style>
    body {
        height: 100vh;
        margin: 0;
    }
    .wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100%;
    }
    .result-body {
        font-size: 14px;
        padding: 5%;
    }
    h1 {
        width: 100%;
        text-align: center;
        margin-bottom: 2em;
        font-size: 20px;
    }
    .result-success {
        color: green;
    }
    .result-fail {
        color: red;
    }
    .result-cancel {
        color: #ffa600;
    }
    .btn {
        display: block;
        -webkit-text-size-adjust: 100%;
        -webkit-tap-highlight-color: transparent;
        box-sizing: border-box;
        margin: 0 auto;
        font-family: 'Open Sans', sans-serif;
        font-weight: 400;
        text-align: center;
        white-space: nowrap;
        vertical-align: middle;
        touch-action: manipulation;
        background-image: none;
        padding: 6px 12px;
        line-height: 1.5;
        border-radius: 4px;
        user-select: none;
        color: #333;
        background-color: #fff;
        border: 1px solid #ccc;
        -webkit-appearance: button;
        cursor: pointer;
        text-decoration: none;
        font-size: 14px;
        width: 50%;
    }
    .btn:hover {
        background-color: #ccc;
        border-color: #000;
    }
    img {
        display: block;
        width: 25%;
        height: auto;
        margin: 0 auto 30px auto;
    }
    @media (max-width: 768px) {
        img {
            width: 30%;
        }
        .btn {
            width: 60%;
        }
    }
    @media (max-width: 576px) {
        img {
            width: 40%;
        }
        .btn {
            width: 100%;
        }
    }

</style>
<?php

$result = '';
if (isset($_GET['result'])) {
    switch (strtolower($_GET['result'])) {
        case 'success':
            $result = '<h1 class="result-success">Вітаємо! Ваш платіж успішний</h1>';
            break;
        case 'cancel':
            $result = '<h1 class="result-cancel">Платіж скасовано за рішенням користувача</h1>';
            break;
        default:
            $result = '<h1 class="result-fail">На жаль сталася помилка під час оплати. Ваш платіж не успішний</h1>';
    }
}
$url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
?>
<div class="wrapper">
    <div class="result-body">
        <img src="/modules/gateways/concordpay/concordpay.svg" alt="ConcordPay">
        <?php echo $result; ?>
        <a class="btn" href="<?php echo $url; ?>">На головну сторінку</a>
    </div>
</div>

