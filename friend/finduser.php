<?php 
require_once("QueryPDO.php"); //Singleton connection bdd & communication + return en JSon

  if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == "GET") { // Verification de la methode de requete

		if(isset($_GET["token"])){ // Verification de la présence des variables en paramètres

			
			if(is_null($IdUser = QueryPDO::getInstance()->getIdByToken($_GET["token"]))){ //Code 4, en cas de parametre: "token" inconnu
				return QueryPDO::getInstance()->ServiceReturnJson("4","Invalid Token");
			}

			//-------------------------------------------------------------------------
			//------------------------------  CODE ------------------------------------
			//------Il faut biensur changer les parametres en fonction du besoin-------



			
					$nombre_de_msg_par_page=10; // On met dans une variable le nombre de messages qu'on veut par page
					 
					// On récupère le nombre total de messages
					 
					$reponse=QueryPDO::getInstance()->query('SELECT COUNT(*) AS amitie FROM friend');
					$total_messages = $reponse->fetch();
					$nombre_messages=$total_messages['amitie'];
					 
					 
					 
					// on détermine le nombre de pages
					$nb_pages = ceil($nombre_messages / $nombre_de_msg_par_page);
					         
					 
					// Puis on fait une boucle pour écrire les liens vers chacune des pages
					echo 'Page : ';
					for ($i = 1 ; $i <= $nb_pages ; $i++)
					{
					    echo '<a href="http://localhost/devweb/projet/SocialNetworkServices/friend/finduser.php?token='.$_GET["token"].'&page=' . $i . '">' . $i . '</a> '; // en dur
					}
 					echo "<br>";
					// Maintenant, on va afficher les messages
					// ---------------------------------------
					 
					if (isset($_GET['page']))
					{
					    $page = $_GET['page']; // On récupère le numéro de la page indiqué dans l'adresse 
					}
					else // La variable n'existe pas, c'est la première fois qu'on charge la page
					{
					    $page = 1; // On se met sur la page 1 (par défaut)
					}
					 
					// On calcule le numéro du premier message qu'on prend pour le LIMIT de MySQL
					$premierMessageAafficher = ($page - 1) * $nombre_de_msg_par_page;
					 
					// On ferme la requête avant d'en faire une autre
					
					$reponse1 = QueryPDO::getInstance()->query('SELECT user.user_name, user.user_firstname, user.user_token FROM user 
																WHERE user.iduser != '.$IdUser.'
																LIMIT ' . $premierMessageAafficher . ', ' . $nombre_de_msg_par_page);

					if(is_object($reponse1)){
						while($donnees1 = $reponse1->fetch()) 
							{	
								$reponse2 = QueryPDO::getInstance()->query('SELECT friend.friend_accepted FROM friend 
																WHERE ( iduser= '.$IdUser.' AND idfriend = '.QueryPDO::getInstance()->getIdByToken($donnees1['user_token']).') 
																OR 
																(iduser= '.QueryPDO::getInstance()->getIdByToken($donnees1['user_token']).' AND idfriend = '.$IdUser.')
																');

								if(is_object($reponse2)){
									
									while($donnees2 = $reponse2->fetch()) 
										{	
											switch ($donnees2['friend_accepted']) {
												    case -1:
												        echo stripslashes(htmlspecialchars($donnees1['user_name'])) . '  -- '.stripslashes(htmlspecialchars($donnees1['user_firstname'])).' --- Declined</p>';
												        break;
												    case 0:
												        echo stripslashes(htmlspecialchars($donnees1['user_name'])) . '  -- '.stripslashes(htmlspecialchars($donnees1['user_firstname'])).'--- Waiting for response</p>';
												        break;
												    case 1:
												        echo stripslashes(htmlspecialchars($donnees1['user_name'])) . '  -- '.stripslashes(htmlspecialchars($donnees1['user_firstname'])).'--- Friend</p>';
												        break;
												}
										    
										}
								}
								else{
									 echo stripslashes(htmlspecialchars($donnees1['user_name'])) . '  -- '.stripslashes(htmlspecialchars($donnees1['user_firstname'])).'</p>';
								}
								
							   


							}
					   
					}


				/*	//-------------------------------- Pour la pagination -----------------------------------------
					
					$reponse2 = QueryPDO::getInstance()->query('SELECT user.user_login FROM user AS U 
																INNER JOIN friend AS F ON U.iduser = F.iduser 
																INNER JOIN user ON user.iduser = F.idfriend 
																WHERE F.iduser= '.$IdUser.' AND F.friend_accepted = 0
																LIMIT ' . $premierMessageAafficher . ', ' . $nombre_de_msg_par_page);

					if(is_object($reponse2)){
						while($donnees = $reponse2->fetch()) 
							{	
								
							    echo 'est amis avec :' . stripslashes(htmlspecialchars($donnees['user_login'])) . '</p>';
							}
					   
					}
						
					    $reponse3 = QueryPDO::getInstance()->query('SELECT user.user_login FROM user AS U 
																	INNER JOIN friend AS F ON U.iduser = F.idfriend 
																	INNER JOIN user ON user.iduser = F.iduser
																	WHERE F.idfriend='.$IdUser.' AND F.friend_accepted = 0 
																	LIMIT ' . $premierMessageAafficher . ', ' . $nombre_de_msg_par_page
																);
					if(is_object($reponse3)){	 
						while($donnees = $reponse3->fetch()) 
							{
								
							    echo '<p> est amis avec :' . stripslashes(htmlspecialchars($donnees['user_login'])) . '</p>';
							}
					}
				
					//-----------------------------------------------------------------------------------------------
					//--------------------------------Pour la liste a renvoyer --------------------------------------
					$jsontab = array();
					$reponse2 = QueryPDO::getInstance()->query('SELECT user.user_login FROM user AS U 
																INNER JOIN friend AS F ON U.iduser = F.iduser 
																INNER JOIN user ON user.iduser = F.idfriend 
																WHERE F.iduser= '.$IdUser.' AND F.friend_accepted = 0
																');

					if(is_object($reponse2)){
						while($donnees = $reponse2->fetch()) 
							{	
								array_push($jsontab, $donnees['user_login']);
							  
							}
					   
					}
						
					    $reponse3 = QueryPDO::getInstance()->query('SELECT user.user_login FROM user AS U 
																	INNER JOIN friend AS F ON U.iduser = F.idfriend 
																	INNER JOIN user ON user.iduser = F.iduser
																	WHERE F.idfriend='.$IdUser.' AND F.friend_accepted = 0 
																	'
																);
					if(is_object($reponse3)){	 
						while($donnees = $reponse3->fetch()) 
							{
								array_push($jsontab, $donnees['user_login']);
							   
							}
					}
					//-----------------------------------------------------------------------------------------------
			//-------------------------------------------------------------------------
			//-------------------------------------------------------------------------

			if(is_null($reponse2 && $reponse3)){ //Si on fait un insert, on verifie que la requete a inseré une ligne, si ce n'est pas le cas la ligne etait déjà présente : code 7
				return QueryPDO::getInstance()->ServiceReturnJson("7","Nothing to update");
			}
			else{
				return QueryPDO::getInstance()->ServiceReturnJson("0",json_encode($jsontab)); //Code 0: tout s'est bien passé. Ici pas de retour donc description
			}*/
			
		}
		else{

			return QueryPDO::getInstance()->ServiceReturnJson("1","Missing parameters"); //code 2: Parametres manquants
		}

	}
	else{

		return QueryPDO::getInstance()->ServiceReturnJson("5","Wrong Request Methode"); // code 1: mauvaise methode de requete
	}


?>