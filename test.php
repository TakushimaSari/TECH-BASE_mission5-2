<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
    </head>
    <body>
        
        <?php
        // ◆◆◆DB接続◆◆◆
        $dsn = 'データベース名';
        $user = 'ユーザー名';
        $password = 'パスワード';
        $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
        
        
        //◆◆◆テーブルの作成◆◆◆
        $sql = "CREATE TABLE IF NOT EXISTS test_text"
        ." ("
        . "id INT AUTO_INCREMENT PRIMARY KEY,"
        . "name char(32),"
        . "comment TEXT,"
        . "password TEXT"
        .");";
        $stmt = $pdo->query($sql);
        
        
        //◆◆◆新規投稿、編集分岐◆◆◆
        if(isset($_POST["submit"]) &&
            !empty($_POST["comment"]) &&
            !empty($_POST["name"])  &&
            !empty($_POST["password"]) &&
            
            $_SERVER["REQUEST_METHOD"] == "POST"){
            $name=$_POST["name"];
            $comment=$_POST["comment"];
            $password=$_POST["password"];
            
            // 編集
            if (!empty($_POST["edit-num-hidden"])){
                $id=$_POST["edit-num-hidden"];
                $sql='UPDATE test_text SET name=:name,comment=:comment,password=:password WHERE id=:id';
                $stmt=$pdo->prepare($sql);
                $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
                $stmt->bindParam(':password', $password, PDO::PARAM_STR);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
            
            //新規
            }else{
                $sql=$pdo -> prepare("INSERT INTO test_text (name, comment, password) VALUES (:name, :comment, :password)");
                $sql -> bindParam(':name', $name, PDO::PARAM_STR);
                $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
                $sql -> bindParam(':password', $password, PDO::PARAM_STR);
                $sql -> execute();
            }
            header("Location: " . $_SERVER["SCRIPT_NAME"]);
            exit;
        }
            
        //◆◆◆削除機能◆◆◆
        //削除フォームの送信の有無で処理を分岐
        if(!empty($_POST['delete-num'])){
            $id=$_POST['delete-num'];
            $delete_password=$_POST['delete-password'];
                
            // パスワード取得
            $sql='select * from test_text where id=:id';
            $stmt=$pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute(); //SQLの実行
            $results=$stmt->fetchAll();
                
            foreach ($results as $row){
                $password_check=$row['password'];
            }
            if($password_check == $delete_password){
                $sql='delete from test_text where id=:id';
                $stmt=$pdo->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
            }
        }
            
        //◆◆◆編集機能(元の投稿を表示）◆◆◆
        $edit_name="";
        $edit_comment ="";
        $edit_num = "";
            
        if(isset($_POST["edit-num"]) && !empty($_POST["edit-num"])){
            
            $edit_num=$_POST["edit-num"];
            $edit_password=$_POST["edit-password"];
            
            $sql='select * from test_text where id=:id';
            $stmt=$pdo->prepare($sql); //SQLを準備
            $stmt->bindParam(':id', $edit_num, PDO::PARAM_INT); //変数指定
            $stmt->execute();    //SQLの実行
            
            $results=$stmt->fetchAll();
            foreach($results as $row) {
                $password_check=$row['password'];
            }
            if ($edit_password == $password_check){
                foreach ($results as $row) {
                    $edit_name=$row['name'];
                    $edit_comment=$row['comment'];
                }
            }else{
                $edit_num=""; //パスワードが違う場合、edit-num-hiddenを空
            }
        }
    ?>
        
        <!--入力フォーム-->
        <form action="" method="post">
            <input type="text" name="name" placeholder="名前" value="<?php echo $edit_name; ?>"><br>
            <input type="text" name="comment" placeholder="コメント" value="<?php echo $edit_comment; ?>"><br>
            <input type="hidden" name="edit-num-hidden" value="<?php echo $edit_num; ?>">
            <input type="text" name="password" placeholder="パスワード">
            <button type="submit" name="submit">送信</button><br><br>
            
            <input type="text" name="delete-num" placeholder="削除対象番号"><br>
            <input type="text" name="delete-password" placeholder="パスワード">
            <button type="submit" name="delete">削除</button><br><br>
            
            <input type="text" name="edit-num" placeholder="編集対象番号"><br>
            <input type="text" name="edit-password" placeholder="パスワード">
            <button type="submit" name="edit">編集</button><br><br>
            
        </form>
        
        
        <?php
        //◆◆◆表示機能◆◆◆
        $sql='SELECT * FROM test_text';
        $stmt=$pdo->query($sql);
        $results=$stmt->fetchAll();
        foreach($results as $row){
            echo $row['id']."\t";
            echo $row['name']."\t";
            echo $row['comment'].'';
        echo "<hr>";
        }
        ?>
    </body>
</html>