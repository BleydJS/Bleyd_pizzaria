<?php
    include_once ("conn.php");

    $method = $_SERVER["REQUEST_METHOD"];

    //resgate dos dados, montagem do pedido
    if ($method === "GET") {

        $bordasQuery = $conn->query("SELECT * FROM bordas;");
        //aqui eu crio uma variavel de $bordasQuery, faço uma conexão e ligo ao "SELECT * FROM bordas;

        $bordas = $bordasQuery->fetchall();
        //agora que eu ja tenho o conteudo dentro de $bordasQuery. agora crio uma variavel para receber ela e executo com o fetchall();
        //o que acontece que ele vai transferir os dados que retornam dessa query para um array, que no caso vai ser o $bordas.
        //por meio desse array consigo colocar os dados la no front

        $massasQuery = $conn->query("SELECT * FROM massas;");
        $massas = $massasQuery->fetchall();


        $saboresQuery = $conn->query("SELECT * FROM sabores;");
        $sabores = $saboresQuery->fetchall();

    
    //criação do pedido
    } else if ($method === "POST") {

        $data = $_POST;

        $borda = $data["borda"];
        $massa = $data["massa"];
        $sabores = $data["sabores"];

        if (count($sabores) > 3) {

            $_SESSION["msg"] = "Selecione no máximo 3 sabores!";
            $_SESSION["status"] = "warning";

        } else {

            //salvando borda e massa na pizza
            $stmt = $conn->prepare("INSERT INTO pizzas(borda_id, massas_id) VALUES (:borda, :massa)");

            //filtrando inputs
            $stmt->bindParam(":borda", $borda, PDO::PARAM_INT);
            $stmt->bindParam(":massa", $massa, PDO::PARAM_INT);

            $stmt->execute();

            //resgatando último id da última pizza
            $pizzaId = $conn->lastInsertId();

            $stmt = $conn->prepare("INSERT INTO pizza_sabor(pizza_id, sabor_id) VALUES (:pizza,:sabor)");


            //repetição até terminar de salvar todos os sabores

            foreach($sabores as $sabor) {

                //filtrando os inputs
                $stmt->bindParam(":pizza", $pizzaId, PDO::PARAM_INT);
                $stmt->bindParam(":sabor", $sabor, PDO::PARAM_INT);

                $stmt->execute();

            }

                //criar o pedido da pizza
                $stmt= $conn->prepare("INSERT INTO pedidos(pizza_id, status_id) VALUES (:pizza,:status)");

                //status -> sempre inicia com 1, que é em produção
                $statusId = 1;

                //filtrar inputs
                $stmt->bindParam(":pizza", $pizzaId);
                $stmt->bindParam(":status", $statusId);

                $stmt->execute();

                //exibir mensagem de sucesso
                $_SESSION["msg"] = "Pedido realizado com sucesso";
                $_SESSION ["status"] = "success";

            // echo "passou da validação";
            // exit;
        }

        header("Location: ..");

    }



?>