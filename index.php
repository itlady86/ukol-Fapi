<?php 
    session_start();
    include "header.php";
    include "config.php";   
    include "nav.php"; 
           
    if(isset($_POST["npridat"])) {
        if(isset($_SESSION["produkty"])) {
            $tempArr_id = array_column($_SESSION["produkty"], "temp_id");
            if(!in_array($_GET["id"], $tempArr_id)) {
                $count = count($_SESSION["produkty"]);
                $tempArr = array (
                    "temp_id" => $_GET["id"],
                    "temp_produkt" => $_POST["nprodukt"],
                    "temp_cena" => $_POST["ncena"],
                    "temp_pocet" => $_POST["npocet"]
                );
                $_SESSION["produkty"][$count] = $tempArr;
            } else { $hlaska = "Tato položka již byla přidána"; }
        } else {
            $tempArr = array (
                "temp_id" => $_GET["id"],
                "temp_produkt" => $_POST["nprodukt"],
                "temp_cena" => $_POST["ncena"],
                "temp_pocet" => $_POST["npocet"]
            );
            $_SESSION["produkty"][0] = $tempArr;
        }
    } else { session_destroy(); }

?>

<!-- HTML -->
        <!-- produkty začátek -->
        <h2 class="text-center text-primary" id="produkty">Nabídka produktů</h2>
        <div class="d-md-flex justify-content-md-around flex-wrap">
            <?php 
               if (mysqli_num_rows($result) > 0) {
                   while($row = mysqli_fetch_array($result)) {
            ?> 
            <form method="post" action="index.php?action=add&id=<?php echo $row["id"]?>">
                <div class="card my-5" style="width: 18rem;">
                   <img src="img/random_product.jpg" class="card-img-top" alt="random-product" title="<?php echo $row["produkt"]?>">
                    <div class="card-body">
                        <h6 class="card-title py-2"><?php echo $row["produkt"]; ?></h6>
                        <p class="card-text">Lorem ipsum dolor sit amet consectetur adipisicing elit. </p>
                        <h6 class="card-title">Cena:  <?php echo number_format($row["cenaZaKus"], 0, ",", ".").",-"; ?></h6>
                        <input class="form-control" type="number" min="0" name="npocet" value="1">
                        <input hidden name="nid" value="<?php echo $row['id'];?>">
                        <input hidden name="nprodukt" value="<?php echo $row["produkt"]?>">
                        <input hidden name="ncena" value="<?php echo $row["cenaZaKus"]?>">
                        <input type="submit" name="npridat" class="btn btn-primary mt-3" style="width:16rem;" value="Vybrat">
                    </div>
                </div>
            </form>
        <?php  } 
        } ?>
        <!-- produkty konec-->


        <?php if($hlaska !=""){?>
            <div class="alert alert-danger col-12 mx-auto my-3 text-center"> <?php echo $hlaska; ?></div>
        <?php } ?>

        <!-- souhrn začátek -->
        <div id="summary">
            <h2 class="text-center mt-5 mx-auto" style="margin-top: 6rem;">Přehled vybraných produktů</h2>
            <hr>
            <table class="table mt-5 table-striped table-bordered mx-auto" style="width: 45rem;"> 
                <thead class="thead-dark text-center">
                    <tr>
                        <th scope="col">Produkt</th>
                        <th scope="col">Počet kusů</th>
                        <th scope="col">Cena za ks</th>
                        <th scope="col">Celková cena</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($_SESSION["produkty"])) { 
                        $total = 0;
                        foreach ($_SESSION["produkty"] as $keys => $values) {  ?>
                            <tr class="text-center">
                                <td align="left"><?php echo $values["temp_produkt"]; ?></td>
                                <td><?php echo $values["temp_pocet"]; ?></td>
                                <td><?php echo number_format($values["temp_cena"],0, ",", ".") . ",-"; ?></td>
                                <td><?php echo number_format($values["temp_pocet"] * $values["temp_cena"],0, ",", ".") .",-"; ?></td>
                            </tr>
                        <?php 
                            $total = $total + ($values["temp_pocet"] * $values["temp_cena"]);
                        } ?>
                        <tr style="font-weight: bold;">
                            <td colspan="3" align="left">Celkem</td>
                            <td align="center"><?php echo number_format($total,0, ",", ".") .",-"; ?></td>
                        </tr>
                        <?php } ?>    
                </tbody>
            </table>
        </div>
       <!-- souhrn konec  -->

       <!--  formulář začátek -->
       <div id="form" style="margin: 6rem 0 4rem;">
        <h2 class="text-center my-3">Údaje o zákazníkovi</h2>
        <hr>
            <form action="summary.php" method="post" class="mx-auto p-5 bg-dark text-white rounded mt-5" style="width: 35rem;">
                <div class="mb-3 mx-5">
                    <label class="form-label">Jméno</label>
                    <input name="njmeno" type="text" class="form-control" id="f-jmeno" placeholder="Josef" required>
                </div>
                <div class="mb-3 mx-5">
                    <label class="form-label">Příjmení</label>
                    <input name="nprijmeni" type="text" class="form-control" id="f-prijmeni" placeholder="Novák" required>
                </div>
                <div class="mb-3 mx-5">
                    <label class="form-label">Ulice</label>
                    <input name="nulice" type="text" class="form-control" id="f-ulice" placeholder="Mánesova 2457/12" required>
                </div>
                <div class="mb-3 mx-5">
                    <label class="form-label">Město</label>
                    <input name="nmesto" type="text" class="form-control" id="f-mesto" placeholder="Praha 1" required>
                </div>
                <div class="mb-3 mx-5">
                    <label class="form-label">PSČ</label>
                    <input name="npsc" type="text" class="form-control" id="f-psc" placeholder="110 01" required>
                </div>
                <div class="text-center mt-5">
                    <input name="nudaje" type="submit" class="btn btn-success" style="width: 15rem;" value="Odeslat k rekapitulaci">
                </div>
            </form>
        </div>
         <!--  formulář konec -->

    </main>
    

    <?php 
        include "footer.php";
    ?>