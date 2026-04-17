<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    exit('Access Denied');
}

if (isset($_FILES['upload']['name'])) {
    $file = $_FILES['upload']['name'];
    $filetmp = $_FILES['upload']['tmp_name'];
    $new_name = time() . '_' . $file;
    $url = '../assets/uploads/' . $new_name;

    if (move_uploaded_file($filetmp, $url)) {
        $function_number = $_GET['CKEditorFuncNum'];
        $url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF'], 2) . '/assets/uploads/' . $new_name;
        $message = 'Tải ảnh lên thành công!';
        echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($function_number, '$url', '$message');</script>";
    }
}
?>