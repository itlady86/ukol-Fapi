<?php
    session_start();
    include "header.php";
    include "config.php";   
    include "nav.php"; 

    function spocitej_dan($adph, $acena) {$count = $acena * ($adph/100); return $count; }

    function format($value) { 
        if(strpos($value, ".") !== false)  { $value = round($value,2); } 
        return $value;
    }

    if(isset($_POST["nudaje"])){
        if(isset($_SESSION["produkty"])) {
            $jmeno = $_POST["njmeno"];
            $prijmeni = $_POST["nprijmeni"];
            $ulice = $_POST["nulice"];
            $mesto = $_POST["nmesto"];
            $psc = $_POST["npsc"];
            $zakaznik = $jmeno . " " . $prijmeni;
            $adresa = $ulice . "<br>" . $psc . "  " . $mesto;
        }
    }

    if(isset($_POST["nprevod"]) || isset($_POST["nudaje"])) {
        $from = $_POST["nfrom"];
        $to = $_POST["nto"];
        $amount = floatval($_POST["namount"]);
        $url = "https://www.cnb.cz/cs/financni-trhy/devizovy-trh/kurzy-devizoveho-trhu/kurzy-devizoveho-trhu/denni_kurz.txt";  
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => 1
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $rows = explode("\n", $response);
        $header_date = array_shift($rows);
        $header = explode("|", array_shift($rows));
        $data = array();
        foreach ($rows as $row) { $row = explode("|", $row); $data[$row[3]] = $row; }
        foreach ($data as $key => $val) {
            $val[4] = str_replace(",", ".", $val[4]);
            if($to === $key) {
                $result = round($amount / floatval($val[4]),2);
                $result = $result ." ". $to;
                break;
            } 
        }
    } 
?>

    <div class="col-md-11 border mx-auto" style="margin: 2rem 0 4rem;">
        <h2 class="text-center my-4 display-4">Rekapitulace</h2>
        <hr style="width: 80%; margin: 0 auto;">
        <div class="card alert alert-danger text-black mx-auto my-5" style="width: 20rem;">
               <div class="card-body">
                   <h4 class="card-title mt-2 mb-4">Zákazník</h4>
                   <h6 class="card-text"><?php echo $zakaznik ?></h6>
                   <p class="card-text"><?php echo $adresa; ?></p>
               </div>
           </div>

        <table class="table mt-5 table-striped table-bordered mx-auto" style="width: 85%;"> 
                <thead class="thead-dark text-center">
                    <tr>
                        <th scope="col">Produkt</th>
                        <th scope="col">Počet kusů</th>
                        <th scope="col">Cena za ks</th>
                        <th scope="col">% DPH</th>
                        <th scope="col">DPH</th>
                        <th scope="col">Celkem</th>
                    </tr>
                </thead>
                <tbody id="tbody">
                    <?php if(!empty($_SESSION["produkty"])) { 
                        $total = 0;
                        foreach ($_SESSION["produkty"] as $keys => $values) {  ?>
                            <?php 
                                $celkemKus = $values["temp_cena"];
                                if($values["temp_pocet"] == 1) { $dan = spocitej_dan($dph, $celkemKus);}
                                else {$dan = spocitej_dan($dph, $celkemKus * $values["temp_pocet"]);}
                                $celkem = $dan + $celkemKus * $values["temp_pocet"];
                            ?>
                            <tr class="text-center">
                                <td align="left"><?php echo $values["temp_produkt"]; ?></td>
                                <td><?php echo $values["temp_pocet"]; ?></td>
                                <td align="right"><?php echo number_format($celkemKus,2,".", " "); ?></td>
                                <td><?php echo $dph; ?></td>
                                <td align="right"><?php echo number_format($dan,2,".", " ");?></td>
                                <td align="right" class="pe-4"><?php echo number_format($celkem,2, ".", " ");?></td>
                            </tr>
                        <?php } ?>
                        <tr style="font-weight: bold;">
                            <td colspan="5" align="left" style="letter-spacing: 0.2rem;">CELKEM</td>
                            <td id="celkem" align="right"></td>
                        </tr>
                        <?php } ?>    
                </tbody>
        </table>

        <div class="mx-auto col-md-8 my-5">
            <h6>Převodník měn:</h6>
            <form method="post" class="d-flex">
                <div class="form-group mx-auto" style="width: 7rem;">
                    <input name="nfrom" type="text" class="form-control text-center" value="CZK">
                </div>
                <div class="form-group mx-auto" style="width: 12rem;">
                    <input name="namount" id="field" type="text" class="form-control text-center" >
                </div>
                <div class="form-group mx-auto">
                    <select name="nto" id="select" style="height: 2.3rem;">
                    </select>
                </div>
                <div class="form-group mx-3 px-2">
                    <input name="nprevod" type="submit" class="btn btn-warning" value="Přepočítej">
                </div>
                <div class="form-group " >
                    <input name="nresult" class="form-control text-center" value="<?php if(isset($_POST['nprevod'])) echo $result ;?>">
                </div>
            </form>
        </div>
    </div>
    
    
    <!--kurzy start -->
    <div>
        <h6 class="text-center small text-muted" style="margin-top: 4rem;">Kurzovní lístek k aktuálnímu datu</h6>
        <div id="kurzy_main" style="border:1px solid silver">
            <marquee id="kurzy_ticker" scrollamount="2" scrolldelay="10" behavior="scroll" loop="infinite" OnMouseOver="this.stop()" OnMouseOut="this.start()">
                <a style="color: black; text-decoration: none;" href="https://www.kurzy.cz/" title="Kurzy měn, akcie, komodity">Kurzy.cz</a> 
                <a style="color: black; text-decoration: none;" href="https://www.kurzy.cz/kurzy-men/" title="Kurzy měn, kurzovní lístek ČNB Česká národní banka" id="kurzy_datum">Kurzy ČNB</a>
                <span id="k__t">
                    <span style="padding-left:20px;padding-right:20px;border-left:2px solid #BBBBBB;border-right:2px solid #DDDDDD;"><a href="https://www.kurzy.cz/kurzy-men/nejlepsi-kurzy/USD-americky-dolar/" id="k__USD" title="americký dolar, USA, USD - nejlepší kurzy bank, kurzy ČNB">americký dolar</a>
                    <span id="kk__USD"></span>0 Kč
                    <img loading="lazy" src="https://img.kurzy.cz/i/flag/arrU.GIF" alt="Změna" id="kz__USD" style="border:0" />
                    <span id="kc__USD"></span>
                <!--datumcas--><span id="kd__USD"></span><!--/datumcas-->
                </span>
                <span style="padding-left:20px;padding-right:20px;border-left:2px solid #BBBBBB;border-right:2px solid #DDDDDD;"><a href="https://www.kurzy.cz/kurzy-men/nejlepsi-kurzy/GBP-britska-libra/" id="k__GBP" title="britská libra, Británie, GBP - nejlepší kurzy bank, kurzy ČNB">britská libra</a>
                    <span id="kk__GBP"></span>0 Kč
                    <img loading="lazy" src="https://img.kurzy.cz/i/flag/arrU.GIF" alt="Změna" id="kz__GBP" style="border:0" />
                    <span id="kc__GBP"></span>
                <!--datumcas--><span id="kd__GBP"></span><!--/datumcas-->
                </span>
                <span style="padding-left:20px;padding-right:20px;border-left:2px solid #BBBBBB;border-right:2px solid #DDDDDD;"><a href="https://www.kurzy.cz/kurzy-men/nejlepsi-kurzy/EUR-euro/" id="k__EUR" title="euro, EMU EURO, EUR - nejlepší kurzy bank, kurzy ČNB">euro</a>
                    <span id="kk__EUR"></span>0 Kč
                    <img loading="lazy" src="https://img.kurzy.cz/i/flag/arrU.GIF" alt="Změna" id="kz__EUR" style="border:0" />
                    <span id="kc__EUR"></span>
                <!--datumcas--><span id="kd__EUR"></span><!--/datumcas-->
                </span>
                <span style="padding-left:20px;padding-right:20px;border-left:2px solid #BBBBBB;border-right:2px solid #DDDDDD;"><a href="https://www.kurzy.cz/kurzy-men/nejlepsi-kurzy/HRK-chorvatska-kuna/" id="k__HRK" title="chorvatská kuna, Chorvatsko, HRK - nejlepší kurzy bank, kurzy ČNB">chorvatská kuna</a>
                    <span id="kk__HRK"></span>0 Kč
                    <img loading="lazy" src="https://img.kurzy.cz/i/flag/arrU.GIF" alt="Změna" id="kz__HRK" style="border:0" />
                    <span id="kc__HRK"></span>
                <!--datumcas--><span id="kd__HRK"></span><!--/datumcas-->
                </span>
                <span style="padding-left:20px;padding-right:20px;border-left:2px solid #BBBBBB;border-right:2px solid #DDDDDD;"><a href="https://www.kurzy.cz/kurzy-men/nejlepsi-kurzy/JPY-japonsky-jen/" id="k__JPY" title="japonský jen, Japonsko, JPY - nejlepší kurzy bank, kurzy ČNB">japonský jen</a>
                    <span id="kk__JPY"></span>0 Kč
                    <img loading="lazy" src="https://img.kurzy.cz/i/flag/arrU.GIF" alt="Změna" id="kz__JPY" style="border:0" />
                    <span id="kc__JPY"></span>
                <!--datumcas--><span id="kd__JPY"></span><!--/datumcas-->
                </span>
                <span style="padding-left:20px;padding-right:20px;border-left:2px solid #BBBBBB;border-right:2px solid #DDDDDD;"><a href="https://www.kurzy.cz/kurzy-men/nejlepsi-kurzy/CAD-kanadsky-dolar/" id="k__CAD" title="kanadský dolar, Kanada, CAD - nejlepší kurzy bank, kurzy ČNB">kanadský dolar</a>
                    <span id="kk__CAD"></span>0 Kč
                    <img loading="lazy" src="https://img.kurzy.cz/i/flag/arrU.GIF" alt="Změna" id="kz__CAD" style="border:0" />
                    <span id="kc__CAD"></span>
                <!--datumcas--><span id="kd__CAD"></span><!--/datumcas-->
                </span>
                <span style="padding-left:20px;padding-right:20px;border-left:2px solid #BBBBBB;border-right:2px solid #DDDDDD;"><a href="https://www.kurzy.cz/kurzy-men/nejlepsi-kurzy/PLN-polsky-zloty/" id="k__PLN" title="polský zlotý, Polsko, PLN - nejlepší kurzy bank, kurzy ČNB">polský zlotý</a>
                    <span id="kk__PLN"></span>0 Kč
                    <img loading="lazy" src="https://img.kurzy.cz/i/flag/arrU.GIF" alt="Změna" id="kz__PLN" style="border:0" />
                    <span id="kc__PLN"></span>
                <!--datumcas--><span id="kd__PLN"></span><!--/datumcas-->
                </span>
                <span style="padding-left:20px;padding-right:20px;border-left:2px solid #BBBBBB;border-right:2px solid #DDDDDD;"><a href="https://www.kurzy.cz/kurzy-men/nejlepsi-kurzy/CHF-svycarsky-frank/" id="k__CHF" title="švýcarský frank, Švýcarsko, CHF - nejlepší kurzy bank, kurzy ČNB">švýcarský frank</a>
                    <span id="kk__CHF"></span>0 Kč
                    <img loading="lazy" src="https://img.kurzy.cz/i/flag/arrU.GIF" alt="Změna" id="kz__CHF" style="border:0" />
                    <span id="kc__CHF"></span>
                <!--datumcas--><span id="kd__CHF"></span><!--/datumcas-->
                </span>
                <span style="padding-left:20px;padding-right:20px;border-left:2px solid #BBBBBB;border-right:2px solid #DDDDDD;"><a href="https://www.kurzy.cz/akcie-cz/burza/" id="k__PX50" title="Burza cenných papírů Praha, PX 50, akcie CZ online"> Burza PX50</a>
                    <span id="kk__PX50"></span>0 Kč
                    <img loading="lazy" src="https://img.kurzy.cz/i/flag/arrU.GIF" alt="Změna" id="kz__PX50" style="border:0" />
                    <span id="kc__PX50"></span>
                <!--datumcas--><span id="kd__PX50"></span><!--/datumcas-->
                </span>
            </marquee>
        </div>
        <script src="https://data.kurzy.cz/export/kurzy-22.js" type="text/javascript"> </script>
    </div>
<!--kurzy end -->

<script>
    function akce() {
        // sčítá poslední sloupec tabulky 
        var celkem = document.getElementById("celkem");
        var tbody = document.getElementById("tbody");
        var field = document.getElementById("field");
        var select = document.getElementById("select");
        var suma = 0;
        var temp;
        var index = 0;
        
        const mena = [ "AUD", "BRL", "BGN", "CNY", "DKK", "EUR", "PHP", "HKD", "HRK", "INR", "IDR",
                       "ISK", "ILS", "JPY", "ZAR", "CAD", "KRW", "HUF", "MYR", "MXN", "XDR", "NOK", 
                       "NZD", "PLN", "RON", "SGD", "SEK", "CHF", "THB", "TRY", "USD", "GBP" ];
        
        for (var r = 0; r < tbody.rows.length-1; r++) {
            value = tbody.rows[r].cells[5].innerHTML;
            value = value.replace(/\s/g, "");
            suma = suma + parseFloat(value);
        }   
        celkem.innerHTML = Math.round(suma) + ".00,-";
        celkem.style="letter-spacing: 0.2rem;";
        
        //hodnotu zapíše i do fieldu
        temp = celkem.innerHTML;
        field.value = temp.substring(0, temp.length-2);
        
        //naplní option jednotlivými měnami
        mena.forEach(function(item){
            var opt = document.createElement("option");
            opt.value = item;
            opt.innerHTML = item;
            select.appendChild(opt);
        })
        //defaultně nastaví EUR
        for (var i = 0; i < mena.length; i++ ) { select.value = mena[5]; }
    }
</script>
</main>
<?php 
    include "footer.php";
?>