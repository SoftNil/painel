<?php
$qtdNav = 2;
if ($pg > $qtdPag) {
    $pg = $qtdPag;
}
$Previous = $pg - 1;
$Next = $pg + 1;
if ($pg == $qtdPag) {
    $NextDisable = 'disabled';
}
if ($pg == 1) {
    $PreviousDisable = 'disabled';
}
?>
<div class="w-100" data-pg-collapsed>
    <form action="<?php echo $pagina; ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" id="pg" name="pg" value="<?php echo $pg; ?>">
        <input type="hidden" id="link" name="link" value="<?php echo $url; ?>">
        <nav class="w-100" aria-label="Paginação" data-pg-collapsed>
            <ul class="pagination justify-content-center ">
                <?php
                if ($qtdPag > 1 && $pg <= $qtdPag) {
                ?>
                    <li class="page-item">
                        <button type="submit" onclick="PegaPG(<?php echo $Previous; ?>)" class="page-link fw-bold <?php echo $PreviousDisable; ?>"><span class="pg-texto"><i class="ri-arrow-left-s-line"></i></span></button>
                    </li>
                    <?php
                    for ($i = 1; $i <= $qtdPag; $i++) {
                        if ($i == $pg) {
                    ?>
                            <li class="page-item">
                                <button type="submit" onclick="PegaPG(<?php echo $i; ?>)" class=" page-link fw-bold active"><?php echo $i; ?></button>
                            </li>
                            <?php
                        } else if ($i < ($pg - $qtdNav) && $i != 1) {
                            if (!$dottedBefore) {
                            ?>
                                <li class="page-item">
                                    <button type="submit" onclick="PegaPG(<?php echo $i; ?>)" class=" page-link fw-bold"><span class="pg-texto">[...]</span></button>
                                </li>
                            <?php
                                $dottedBefore = true;
                            }
                        } else if ($i > ($pg + $qtdNav) && $i < $qtdPag) {
                            if (!$dottedAfter) {
                            ?>
                                <li class="page-item">
                                    <button type="submit" onclick="PegaPG(<?php echo $i + $pg; ?>)" class=" page-link fw-bold"><span class="pg-texto">[...]</span></button>
                                </li>
                            <?php
                                $dottedAfter = true;
                            }
                        } else {
                            ?>
                            <li class="page-item">
                                <button type="submit" onclick="PegaPG(<?php echo $i; ?>)" class=" page-link fw-bold"><?php echo $i; ?></button>
                            </li>
                    <?php
                        }
                    }
                    ?>
                    <li class="page-item">
                        <button type="submit" onclick="PegaPG(<?php echo $Next; ?>)" class=" page-link fw-bold <?php echo $NextDisable; ?>"><span class="pg-texto"><i class="ri-arrow-right-s-line"></i></span></button>
                    </li>
                <?php
                }
                ?>
            </ul>
        </nav>
    </form>
</div>