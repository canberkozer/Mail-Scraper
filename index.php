<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <link rel="stylesheet" type="text/css" href="style.css" />
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
        <script type="text/javascript" src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
        <script type="text/javascript">
            $(function () {
                $('#run').click(function () {
                    if ($("#link").val() != "") {
                        $(this).fadeOut();
                        $("#script_status").fadeIn();
                        $("#script_finished").remove();
                        $(".error").remove();
                    }
                });
            });
        </script> 
    </head>
    <body>			
        <div id="container" >
            <div id="wrapper">
                <h1 class="text-center">Pro Mail Scraper | CODESSIONAL</h1><br>
                <hr>
                <h3 class="text-center">Yazılım ile ilgili destek için <a href="mailto:codessional@gmail.com">burdan</a> mail atabilirsiniz ya da <a href="http://www.instagram.com/codessionak">Instagram'dan</a> mesaj atabilirsiniz.</h3>
                <hr>
                <div id="form" >
                    <form  action="" autocomplete="on" method="post" enctype="multipart/form-data" > 
                        <div id="input_text" class="form-group">
                            <label>Başlangıç Sayfası</label>
                            <input class="form-control" type="number" id="start_page" name="start_page" required="required" value="2" />
                            <br /><br />
                            <label>Bitiş Sayfası</label>
                            <input class="form-control" type="number" id="end_page" name="end_page" required="required" value="10" />
                            <br /><br />
                            <label>Dosya ismi "input.csv" olmalıdır.</label><br><br>
                            <input class="input-group-prepend" type="file" name="file" id="file">
                        </div>
                        <br />
                        <div id="input_submit" class="form-group">
                            <span class="btn btn-primary rounded"> 
                                <input class="btn btn-primary" type="submit" name="run" id="run" value="Çalıştır" /> 
                            </span>
                            <div id="script_status" >
                                <div id="progress_bar" >
                                    <p>Veriler çekiliyor...</p>
                                    <img src="progress_bar.gif" />
                                </div>
                            </div>
                        </div> 

                        <input name="submited" value="submited" type="hidden" />
                    </form>
                    <?php
                    if (isset($_POST["run"]) && ($_POST["submited"] == "submited")) {

                        if ($_FILES["file"]["tmp_name"]) {
// set display errors status
                            ini_set('display_errors', 1); // 1-turn on all error reporings 0-turn off all error reporings
                            error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

// change max execution time to unlimitied
                            ini_set('max_execution_time', 0);

// include simple html dom parser
                            require_once "simple_html_dom.php";

// field delimiter in output file
                            $delimiter = ",";

// output filename
                            $file = "output.csv";

// contact slugs                        
                            $contact_slugs = contact_slugs();

// counter                        
                            $counter = 0;

// included mails
                            $included_mails = array();


// start page
                            $start_page = trim(strip_tags($_POST["start_page"]));

// end page
                            $end_page = trim(strip_tags($_POST["end_page"]));

// open file for writing final results
                            $handler = @fopen($file, "w");

                            $header = "Email" . $delimiter .
                                    "Page Title" . $delimiter .
                                    "Searching Keywords" . $delimiter .
                                    "Google Page Rank" . $delimiter .
                                    "Website" . $delimiter .
                                    "\n";

                            fwrite($handler, $header);
                            fclose($handler);

// upload input file                        
                            $input_file = realpath(dirname(__FILE__)) . "/input.csv";
                            move_uploaded_file($_FILES["file"]["tmp_name"], $input_file);

// open file for reading
                            $input_file_handle = @fopen($input_file, "r");

// loop through file with links for scraping
                            while (($data = fgetcsv($input_file_handle, 0, "\n")) !== FALSE) {
                                $position = $start_page * 10 + 1;
                                $searching_keyword = trim($data[0]);

                                for ($page = $start_page; $page <= $end_page; $page++) {
                                    scrap_google_results($searching_keyword, $page);
                                }
                            }

                            echo "<div id='script_finished'><br /><hr /><b>Veri çekme işlemi tamamlandı!</b>";
                            echo "<hr />
                             <p><a href='" . $file . "' >Sonuç dosyasını burdan indirebilirsiniz..</a></p>
                             <hr /></div>";
                        } else {
                            echo '<p class="error">Lütfen Input dosyası yükleyiniz!</p>';
                        }
                    }
                    ?> </div>
            </div>
        </div>
    </body>
</html>

<?php

// define functions
function scrap_google_results($searching_keyword, $page) {

    global $position;

    $start = $page * 10;

    $scrap_url = "https://www.google.com/search?q=" . urlencode($searching_keyword) . "&hs=Lgz&channel=fs&ei=dBloW6-1LKyKmwWc2IHIDw&start=$start&sa=N&biw=1299&bih=616";

    $html = get_html($scrap_url);

    if ($html && is_object($html) && isset($html->nodes)) {

        $items = $html->find(".g .rc h3.r");

// loop through items on current page
        foreach ($items as $item) {
            $item_elem = $item->find("a", 0);
            if ($item_elem) {
                $item_url = $item_elem->href;
                scrap_info($item_url, $searching_keyword, $position);
                $position++;
            }
        }

        $html->clear();
    }
}

function scrap_info($item_url, $searching_keyword, $position) {

    global $file;
    global $delimiter;
    global $counter;
    global $included_mails;
    $handler = fopen($file, "a");

    $html = get_html($item_url);

    if ($html && is_object($html) && isset($html->nodes)) {

        $page_title = get_value($html, "title", 0, "text");

        $website = get_domain($item_url);

        $counter = 0;

        $email = get_email($html, $website);

        if ($email && !in_array($email, $included_mails)) {

            $csv_line = "";

            $csv_line .= quote_string($email);
            $csv_line .= $delimiter . quote_string($page_title);
            $csv_line .= $delimiter . quote_string($searching_keyword);
            $csv_line .= $delimiter . quote_string($position);
            $csv_line .= $delimiter . quote_string($website);

// write in file
            fwrite($handler, $csv_line . "\n");

            $included_mails[] = $email;
        }

        $html->clear();
    }

    fclose($handler);
}

function quote_string($string) {
    $string = str_replace('"', "'", $string);
    $string = str_replace('&amp;', '&', $string);
    $string = str_replace('&nbsp;', ' ', $string);
    $string = preg_replace('!\s+!', ' ', $string);
    return '"' . trim($string) . '"';
}

function get_html($url) {

    $html = "";

    $cookie = realpath(dirname(__FILE__)) . "/cookie.txt";

    file_put_contents($cookie, "");

    if ($curl = curl_init()) {
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_COOKIEFILE, $cookie);
        curl_setopt($curl, CURLOPT_COOKIEJAR, $cookie);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64; rv:58.0) Gecko/20100101 Firefox/58.0');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 8);
        curl_setopt($curl, CURLOPT_REFERER, $url);
        $curl_html = curl_exec($curl);
        $html = str_get_html($curl_html);
        unset($curl_html);
        curl_close($curl);
    }

    return $html;
}

function get_value($element, $selector_string, $index, $type = "text") {
    $value = "";
    $cont = $element->find($selector_string, $index);
    if ($cont) {
        if ($type == "href") {
            $value = $cont->href;
        } elseif ($type == "src") {
            $value = $cont->src;
        } elseif ($type == "text") {
            $value = trim($cont->plaintext);
        } elseif ($type == "content") {
            $value = trim($cont->content);
        } else {
            $value = $cont->innertext;
        }
    }

    return trim($value);
}

function get_email($html, $website) {

    global $counter;
    global $contact_slugs;

    if (!$html && isset($contact_slugs[$counter])) {
        $contact_url = $website . "/" . $contact_slugs[$counter];
        $html = get_html($contact_url);
    }

    $email = "";

    if ($html && is_object($html) && isset($html->nodes)) {

        $links = $html->find("a");
        foreach ($links as $link) {
            $link_href = $link->href;
            if (strpos($link_href, "mailto:") !== FALSE) {
                if (strpos($link->plaintext, "@") !== FALSE) {
                    $email = trim($link->plaintext);
                } else {
                    $email = str_replace("mailto:", "", $link_href);
                }

                $email = strtolower($email);
                if (strpos($email, "?subject") !== FALSE) {
                    $email_parts = explode("?subject", $email);
                    $email = $email_parts[0];
                }
                break;
            }
        }

        if (!$email) {
            $body = $html->find('body', 0);
            if ($body) {
                $parts = explode(' ', $body->plaintext);
                foreach ($parts as $part) {
                    $email = trim($part);
                    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        break;
                    }
                }
            }
        }

        if (!$email && (isset($contact_slugs[$counter]))) {
            $counter++;
            $email = get_email("", $website);
        }
    }

    $email = strip_tags($email);
    $email = trim(str_replace("&nbsp;", "", $email));

    if (strpos($email, "@") !== FALSE) {
        return $email;
    } else {
        return '';
    }
}

function get_contact_url($html) {

    $url = "";

    $links = $html->find("a");
    foreach ($links as $link) {
        $link_text = strtolower($link->plaintext);
        if ((stripos($link_text, "contact") !== FALSE) || (stripos($link_text, "support") !== FALSE) || (stripos($link_text, "message") !== FALSE) || (stripos($link_text, "help") !== FALSE)) {
            $url = $link->href;
            break;
        }
    }

    return $url;
}

function get_domain($url) {
    $parts = explode("/", $url);
    return $parts[0] . "//" . $parts[2];
}

function contact_slugs() {

    $contact_slugs = array(
        "contact",
        "about",
        "contact.html",
        //"info",
        //"about",
        //"about-us",
        //"help",
        //"support",
        ""
    );

    return $contact_slugs;
}
