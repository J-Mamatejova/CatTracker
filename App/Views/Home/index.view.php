<?php

/** @var \Framework\Support\LinkGenerator $link */
?>

<div class="container-fluid">
    <div class="row">
        <div class="col mt-5">
            <div class="text-center py-4">
                <h2>Pomôžte nám sledovať a chrániť uličné mačky</h2>
                <h3>Version <?= App\Configuration::FW_VERSION ?></h3>

                <!-- Two images with text wrapping: left and right floats -->
                <img src="<?= $link->asset('images/catPic1.jpg') ?>" alt="catPic1" class="img-fluid float-start me-4 mb-3"
                    style="max-width:400px; padding:12px; background:#fff; border-radius:8px; border:1px solid #eee;">
                <img src="<?= $link->asset('images/catPic2.jpg') ?>" alt="catPic2" class="img-fluid float-end ms-4 mb-3"
                    style="max-width:400px; padding:12px; background:#fff; border-radius:8px; border:1px solid #eee;">

                <p class="text-start">
                    Útulky a záchranné stanice sú často preplnené a nie všetky mačky sa dajú umiestniť do stáleho domova.
                    Mnohé z nich žijú vo voľnej prírode a potrebujú našu podporu, aby prežili a mali čo najlepšiu
                    starostlivosť.
                </p>

                <p class="text-start">
                    <strong>Cat Tracker</strong> umožňuje každému prispieť k tvorbe databázy uličných mačiek. Môžete:
                </p>

                <ul class="text-start">
                    <li>poslať informácie o mačke, ktorú ste stretli,</li>
                    <li>skontrolovať, či už bola dokumentovaná,</li>
                    <li>pozrieť si na mape, kde sa nachádza viac mačiek a ktoré miesta potrebujú pomoc.</li>
                </ul>

                <p class="text-start">
                    Vďaka vašim príspevkom môžeme lepšie sledovať tieto mačky, koordinovať odchyt pre kastráciu,
                    zabezpečiť veterinárnu starostlivosť alebo im jednoducho doplniť jedlo tam, kde je potrebné.
                </p>

                <p class="text-start">
                    Spoločne môžeme urobiť život uličných mačiek bezpečnejším a zdravším.
                </p>

                <!-- clear floats so the following content (Authors section) starts below images -->
                <div class="clearfix"></div>

            </div>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col text-center">
            <h4>Authors</h4>
            <div>
                <a href="mailto:mamatejova@stud.uniza.sk">Janka Mamatejová</a><br>
            </div>
        </div>
    </div>
</div>