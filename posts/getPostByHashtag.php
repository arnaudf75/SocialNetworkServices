<?php
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == "GET") {
        if (isset($_SESSION['user_token'])) {
            $tabParams[':token'] = $_SESSION['user_token'];
            if (isset($_GET['hashtag'])) {
                $tabParams[':hashtag'] = $_GET['hashtag'];
                $bdd = new PDO('mysql:host=localhost;dbname=socialnetwork','root','');
                $requete = $bdd->prepare("SELECT *
                                          FROM post P
                                          WHERE content_post LIKE %:hashtag%
                                          AND ( idUser IN (
                                                    SELECT idfriend
                                                    FROM friend
                                                    WHERE iduser = (SELECT iduser FROM user WHERE user_token = :token)
                                                    AND friend_accepted = 1
                                                )
                                                OR iduser = :idUser)
                                          ORDER BY post_date DESC");
                if ($requete && $requete->execute($tabParams)) {
                    $codeRetour = 0;
                    while ($ligne = $getRequetes->fetch(PDO::FETCH_ASSOC)) {
                        $result[] = $ligne;
                    }
                }
                echo (json_encode($result, JSON_PRETTY_PRINT));
                unset($requete);
                unset($bdd);
            }
        }
    }
?>