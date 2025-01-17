<?php
declare(strict_types=1);
require_once("src/web/classes/Loader.php");

Loader::header();

echo '<div class="loader-wrapper">
<span class="loader"><span class="loader-inner"></span></span>
</div>' ;

echo    '<script>
    window.addEventListener("load", function(){
        const loader = document.querySelector(".loader-wrapper");
        loader.classList.add("fade");
    });
</script>';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    /*$address = filter_var($_POST['address'], FILTER_SANITIZE_STRING);
    $surface = filter_var($_POST['surface'], FILTER_SANITIZE_STRING);
    $rooms = filter_var($_POST['rooms'], FILTER_SANITIZE_STRING);
    $floor = filter_var($_POST['floor'], FILTER_SANITIZE_STRING);
    $parking = filter_var($_POST['parking'], FILTER_SANITIZE_STRING);
    $balcony = filter_var($_POST['balcony'], FILTER_SANITIZE_STRING);
    $terrace = filter_var($_POST['terrace'], FILTER_SANITIZE_STRING);
    $state = filter_var($_POST['state'], FILTER_SANITIZE_STRING);*/


    $targetDir = "uploads/"; 
    //on supprime les anciennes images du dossier uploads et results
    $files = glob($targetDir.'*'); 
    $files2 = glob('results/*'); 
    $files = array_merge($files, $files2);
    foreach($files as $file){ 
        if(is_file($file))
            unlink($file); 
    }
    // on vide le fichier rapport.csv
    $file = fopen("rapport.csv", "w");
    fclose($file);

    //on upload les nouvelles images dans le dossier uploads
    $error=array();
    $files = $_FILES['files'];
    $allowed = array('png', 'jpg', 'gif','jpeg');
    foreach($_FILES["files"]["tmp_name"] as $key=>$tmp_name) {
        //on sanitize les noms des images avec filter_var
        
        $file_name= filter_var($_FILES["files"]["name"][$key], FILTER_SANITIZE_STRING);
        $file_tmp=$_FILES["files"]["tmp_name"][$key];
        $filename = uniqid();
        $ext=pathinfo($file_name,PATHINFO_EXTENSION);
    
        if(in_array($ext,$allowed)) {
            move_uploaded_file($file_tmp=$_FILES["files"]["tmp_name"][$key], $targetDir.$filename.".".$ext);
        }
        else {
            array_push($error,"$file_name, ");
        }
    }

    echo "<div class='content'>";
    echo "<h1>Les résultats</h1>";

    //si dossier uploads n'est pas vide
    if (count(glob($targetDir."*")) > 0) {
        //on trouve l'executabe de python
        $exec = "C:/Python/Python310/python.exe";
        //on utilise le script python
        $command = escapeshellcmd("$exec src/ai/analyse.py");
        shell_exec($command);

        //on affiche les images avec les resultats de results.txt
        $file = fopen("results.txt", "r");
        $i = 0;
        while(!feof($file)) {
            $line = fgets($file);
            if ($line == "") {
                break;
            }
            $res = explode(";", $line);
            $label = $res[0];
            $img = $res[1];
            echo "<h2>$label</h2>";
            echo "<img src='$img' alt='image $i' width='40%' height='auto'><br>";
            echo "<h2>L'image " . $i+1 . ' contient : </h2>';
            // on affiche la liste des objets que contient l'image s'il y en a
            if (count($res)>2){
                echo $res[2];
            }
            $i++;
            echo "<br><br>";
        }
            
        fclose($file);

        // on fait un bouton pour télécharger les résultats
        echo "<br><a class='button' href='rapport.csv' download>Télécharger le rapport</a><br>";
        
        //on supprime le fichier results.txt
        unlink("results.txt");

    }else{
        echo "Aucune image n'a été uploadée";
    }
    echo "</div>";
    
}else{

    //les formulaires
    //on commence a charger des images
    echo <<<HTML
        <div class="content">
            <h1>Votre projet</h1>
            <p>Format: .jpeg, .jpg, .png, .gif</p>
            <form action="index.php" method="post" enctype="multipart/form-data">
                <input type="file" name="files[]" id="files" data-multiple-caption="{count} fichiers choisi" multiple>
                <label for="files">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="17" viewBox="0 0 20 17"><path d="M10 0l-5.2 4.9h3.3v5.1h3.8v-5.1h3.3l-5.2-4.9zm9.3 11.5l-3.2-2.1h-2l3.4 2.6h-3.5c-.1 0-.2.1-.2.1l-.8 2.3h-6l-.8-2.2c-.1-.1-.1-.2-.2-.2h-3.6l3.4-2.6h-2l-3.2 2.1c-.4.3-.7 1-.6 1.5l.6 3.1c.1.5.7.9 1.2.9h16.3c.6 0 1.1-.4 1.3-.9l.6-3.1c.1-.5-.2-1.2-.7-1.5z"></path></svg>
                    <span>Choisissez des images</span>
                </label>


                <br><br>
                <input type="submit" value="Analyser">
            </form>
        </div>
    HTML;

    /*
    <h1>Quelle est l'adresse du bien à estimer ?</h1>
            <p>(Facultatif)</p>
            <input type="text" name="address" placeholder="Quelle est l’adresse du bien dont vous souhaitez estimer le loyer ?">

            <h1>Détails</h1>
            <p>(Facultatif)</p>
            <input type="text" name="surface" placeholder="Quelle est la surface du bien ?"><br>
            <input type="text" name="rooms" placeholder="Combien de pièces ?"><br>
            <input type="text" name="floor" placeholder="Quel étage ?"><br>

            <h1>Annexes</h1>
            <p>(Facultatif)</p>
            <input type="text" name="parking" placeholder="Quel type de parking ?"><br>
            <input type="text" name="balcony" placeholder="Quel type de balcon ?"><br>
            <input type="text" name="terrace" placeholder="Quel type de terrasse ?"><br>

            <h1>Etat</h1>
            <p>(Facultatif)</p>
            <select name="" id="">
                <option value="1">Neuf</option>
                <option value="2">Bon</option>
                <option value="3">Moyen</option>
                <option value="4">Mauvais</option>
            </select>
    */
    
    echo "<script src='src/web/js/main.js'></script>";
}


?>