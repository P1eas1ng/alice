<?php
function find_phone($phone){
    $text = file_get_contents( 'https://zvonili.com/phone/'.$phone);
    preg_match( '/<body><div class="container"><div class="row"><div class="col-lg-9 p-2" itemscope="" itemtype="http://schema.org/Place"><div class="mt-3">(.*?)<\\div></div></div></div></body>/is' , $text , $title );
    return $title[1];
}
if (!isset($_REQUEST)) return;
//Получаем и декодируем уведомление
$dataRow = file_get_contents('php://input');
$data = json_decode($dataRow);
//Подготовливаем ответ Алисе
if ($data->request->original_utterance == "" && $data->session->message_id == 0)
{
    $answer = array(
        "response" => array(
            "text" => "Запусти навык сказав Кама Кепарь",
            "tts" => "",
            "buttons" => array(),
            "end_session" => false,
        ),
        "session" => array(
            "session_id" => $data->session->session_id,
            "message_id" => $data->session->message_id,
            "user_id" => $data->session->user_id,
        ),
        "version" => $data->version,
    );
}
else
{
    $answer = array(
        "response" => array(
            "text" => "",
            "tts" => "",
            "buttons" => array(),
            "end_session" => false,
        ),
        "session" => array(
            "session_id" => $data->session->session_id,
            "message_id" => $data->session->message_id,
            "user_id" => $data->session->user_id,
        ),
        "version" => $data->version,
    );
    $orig = $data->request->original_utterance;
    $opt = $data->request->payload->opt;
    $orig = trim($orig);
    $orig = strtolower($orig);
    if ($orig == 'Запусти Навык Кама Кепарь' || $orig == 'Кама Кепарь')
    {
        $answer['response']['text'] = 'Добро пожаловать! Выбери: чем больше всего любишь заниматься и что лучше всего умеешь делать.';
        $answer['response']['tts'] = 'Добро пожаловать! Выбери: - чем больше всего любишь заниматься и что лучше всего умеешь делать. - Нажми на кнопку, или произнеси её название.';
    }
    elseif ($opt == 'write' || if (strpos($orig, 'найди телефон') !== false))
    {
        $peremi = str_replace("найди телефон ", "", $orig);
        $phoneansw = find_phone($peremi);
        $answer['response']['text'] = $phoneansw;
        $answer['response']['tts'] = $phoneansw;
        $answer['response']['buttons'] = array(
            array(
                'title' => $phoneansw,
            ),
        );
    }
    elseif ($orig != '' || $orig != 'запусти навык выбор заработка в интернете' || $orig != 'запусти навык подобрать работу в интернете' || $orig != 'писать' || $orig != 'биржа е т икс т' || $orig != 'биржа текст саль' || $orig != 'биржа адвего' || $orig != 'фотографировать' || $orig != 'фотобанк лори' || $orig != 'фотобанк отражение' || $orig != 'другой выбор' || $orig != 'скажи проверенный сайт для заработка' || $orig != 'помощь')
    {
        if ($data->session->message_id == ($_SESSION['id'] + 1))
        {
            $answer['response']['text'] = 'Ошибка! Пожалуйста, повтори, или нажми на кнопку "Помощь"!';
            $answer['response']['tts'] = 'Извини, я не расслышала! - Пожалуйста, повтори, - или нажми на кнопку "Помощь"!';
            $answer['response']['buttons'] = array(
                array(
                    'title' => 'Помощь',
                    'payload' => array('opt' => 'help'),
                ),
            );
        }
        elseif ($data->session->message_id == ($_SESSION['id'] + 2) || $data->session->message_id == ($_SESSION['id'] + 4))
        {
            $answer['response']['text'] = 'Это ошибка!';
            $answer['response']['tts'] = 'Ошибка ввода!';
            $answer['response']['buttons'] = array(
                array(
                    'title' => 'Странная ошибка',
                    'url' => '.',
                ));
            if (isset($_SESSION['id']))
            {
                unset($_SESSION['id']);
                //session_destroy();
            }
        }
        else
        {
            $id = $data->session->session_id;
            session_id($id);
            session_start();
            if (!isset($_SESSION['id'])) $_SESSION['id'] = [];
            array_push($_SESSION['id'], $data->session->message_id);
            $answer['response']['text'] = 'Скажите найди телефон 8916237192';
            $answer['response']['tts'] = 'Скажите найди телефон 8916237192';
            $answer['response']['buttons'] = array(
                array(
                    'title' => 'найди телефон',
                    'payload' => array('opt' => 'найди телефон'),
            );
        }
    }
    else
    {
        if ($opt == '')
        {
            $answer['response']['text'] = 'Вы ничего не сделали!';
            $answer['response']['tts'] = 'Вы ничего не сделали';
            $answer['response']['buttons'] = array(
                array(
                    'title' => 'Пусто',
                ));
        }
    }
}
header('Content-Type: application/json');
echo json_encode($answer);
