<?php
//generarCuadro.php
    /******************************** Convertir Numero a Letras ***********************/

        class NumeroALetras
        {
            /**
             * @var array
             */
            private $unidades = [
                '',
                'UNO ',
                'DOS ',
                'TRES ',
                'CUATRO ',
                'CINCO ',
                'SEIS ',
                'SIETE ',
                'OCHO ',
                'NUEVE ',
                'DIEZ ',
                'ONCE ',
                'DOCE ',
                'TRECE ',
                'CATORCE ',
                'QUINCE ',
                'DIECISÉIS ',
                'DIECISIETE ',
                'DIECIOCHO ',
                'DIECINUEVE ',
                'VEINTE ',
            ];

            /**
             * @var array
             */
            private $decenas = [
                'VEINTI',
                'TREINTA ',
                'CUARENTA ',
                'CINCUENTA ',
                'SESENTA ',
                'SETENTA ',
                'OCHENTA ',
                'NOVENTA ',
                'CIEN ',
            ];

            /**
             * @var array
             */
            private $centenas = [
                'CIENTO ',
                'DOSCIENTOS ',
                'TRESCIENTOS ',
                'CUATROCIENTOS ',
                'QUINIENTOS ',
                'SEISCIENTOS ',
                'SETECIENTOS ',
                'OCHOCIENTOS ',
                'NOVECIENTOS ',
            ];

            /**
             * @var array
             */
            private $acentosExcepciones = [
                'VEINTIDOS'  => 'VEINTIDÓS ',
                'VEINTITRES' => 'VEINTITRÉS ',
                'VEINTISEIS' => 'VEINTISÉIS ',
            ];

            /**
             * @var string
             */
            public $conector = 'CON';

            /**
             * @var bool
             */
            public $apocope = false;
            /**
             * Formatea y convierte un número a letras en formato facturación electrónica.
             *
             * @param int|float $number
             * @param int       $decimals
             * @param string    $currency
             *
             * @return string
             */
            public function toInvoice($number, $decimals = 2, $currency = '')
            {
                $this->checkApocope();

                $number = number_format($number, $decimals, '.', '');

                $splitNumber = explode('.', $number);

                $splitNumber[0] = $this->wholeNumber($splitNumber[0]);

                if (!empty($splitNumber[1])) {
                    $splitNumber[1] .= '/100 ';
                } else {
                    $splitNumber[1] = '00/100 ';
                }

                return $this->glue($splitNumber) . mb_strtoupper($currency, 'UTF-8');
            }

            //Valida si debe aplicarse apócope de uno.
            private function checkApocope()
            {
                if ($this->apocope === true) {
                    $this->unidades[1] = 'UN ';
                }
            }

            //Formatea la parte entera del número a convertir.
            private function wholeNumber($number)
            {
                if ($number == '0') {
                    $number = 'CERO ';
                } else {
                    $number = $this->convertNumber($number);
                }

                return $number;
            }

            //Concatena las partes formateadas del número convertido.
            private function glue($splitNumber)
            {
                return implode(' ' . mb_strtoupper($this->conector, 'UTF-8') . ' ', array_filter($splitNumber));
            }

            //Convierte número a letras.
            private function convertNumber($number)
            {
                $converted = '';

                if (($number < 0) || ($number > 999999999)) {
                    throw new ParseError('Wrong parameter number');
                }

                $numberStrFill = str_pad($number, 9, '0', STR_PAD_LEFT);
                $millones = substr($numberStrFill, 0, 3);
                $miles = substr($numberStrFill, 3, 3);
                $cientos = substr($numberStrFill, 6);

                if (intval($millones) > 0) {
                    if ($millones == '001') {
                        $converted .= 'UN MILLÓN ';
                    } elseif (intval($millones) > 0) {
                        $converted .= sprintf('%sMILLONES ', $this->convertGroup($millones));
                    }
                }

                if (intval($miles) > 0) {
                    if ($miles == '001') {
                        $converted .= 'MIL ';
                    } elseif (intval($miles) > 0) {
                        $converted .= sprintf('%sMIL ', $this->convertGroup($miles));
                    }
                }

                if (intval($cientos) > 0) {
                    if ($cientos == '001') {
                        $this->apocope === true ? $converted .= 'UN ' : $converted .= 'UNO ';
                    } elseif (intval($cientos) > 0) {
                        $converted .= sprintf('%s ', $this->convertGroup($cientos));
                    }
                }

                return trim($converted);
            }

            private function convertGroup($n)
            {
                $output = '';

                if ($n == '100') {
                    $output = 'CIEN ';
                } elseif ($n[0] !== '0') {
                    $output = $this->centenas[$n[0] - 1];
                }

                $k = intval(substr($n, 1));

                if ($k <= 20) {
                    $unidades = $this->unidades[$k];
                } else {
                    if (($k > 30) && ($n[2] !== '0')) {
                        $unidades = sprintf('%sY %s', $this->decenas[intval($n[1]) - 2], $this->unidades[intval($n[2])]);
                    } else {
                        $unidades = sprintf('%s%s', $this->decenas[intval($n[1]) - 2], $this->unidades[intval($n[2])]);
                    }
                }

                $output .= array_key_exists(trim($unidades), $this->acentosExcepciones) ?
                    $this->acentosExcepciones[trim($unidades)] : $unidades;

                return $output;
            }
        }


        /******************************** Agregar 00 al importe ***********************/
        function agregarPuntosMiles($valor)
            {
                $partes = explode('.', $valor);
                $entero = $partes[0];
                $decimal = isset($partes[1]) ? $partes[1] : '';

                $entero_formateado = number_format($entero, 0, ',', '.');

                $importe_formateado = $entero_formateado;

                if (!empty($decimal)) {
                    $importe_formateado .= ',' . $decimal;
                } else {
                    $importe_formateado .= ',00';
                }

                return $importe_formateado;
            }

        function agregarCerosDecimales($valor)
            {
                $partes = explode('.', $valor);
                $entero = $partes[0];
                $decimal = isset($partes[1]) ? $partes[1] : '';

                if (empty($decimal)) {
                    $valor .= ',00';
                } elseif (strlen($decimal) == 1) {
                    $valor .= '0';
                }

                return $valor;
            }

    


    require_once 'vendor/autoload.php'; // Carga la autoloading de Composer
    use Dompdf\Dompdf;

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
        if (isset($_POST['btn_texto_transferencias1'])) {
            textoTransferencia($_POST['nroVale1'], $_POST['nroTransferencia1'], $_POST['razon1'], $_POST['importeFacturado1'], $_POST['opcion1']);
        } elseif (isset($_POST['btn_texto_transferencias2'])) {
            textoTransferencia($_POST['nroVale2'], $_POST['nroTransferencia2'], $_POST['razon2'], $_POST['importeFacturado2'], $_POST['opcion2']);
        } elseif (isset($_POST['btn_texto_transferencias3'])) {
            textoTransferencia($_POST['nroVale3'], $_POST['nroTransferencia3'], $_POST['razon3'], $_POST['importeFacturado3'], $_POST['opcion3']);
        } elseif (isset($_POST['btn_texto_transferencias4'])) {
            textoTransferencia($_POST['nroVale4'], $_POST['nroTransferencia4'], $_POST['razon4'], $_POST['importeFacturado4'], $_POST['opcion4']);
        }elseif (isset($_POST['btn_texto_transferencias5'])) {
            textoTransferencia($_POST['nroVale5'], $_POST['nroTransferencia5'], $_POST['razon5'], $_POST['importeFacturado5'], $_POST['opcion5']);
        }elseif (isset($_POST['btn_texto_transferencias6'])) {
            textoTransferencia($_POST['nroVale6'], $_POST['nroTransferencia6'], $_POST['razon6'], $_POST['importeFacturado6'], $_POST['opcion6']);
        }elseif (isset($_POST['btn_texto_transferencias7'])) {
            textoTransferencia($_POST['nroVale7'], $_POST['nroTransferencia7'], $_POST['razon7'], $_POST['importeFacturado7'], $_POST['opcion7']);
        }elseif (isset($_POST['btn_texto_transferencias8'])) {
            textoTransferencia($_POST['nroVale8'], $_POST['nroTransferencia8'], $_POST['razon8'], $_POST['importeFacturado8'], $_POST['opcion8']);
        }

       elseif (isset($_POST['btn_cuadro_retenciones1'])) {
            cuadroRetenciones($_POST['nroVale1'], $_POST['cuit1'], $_POST['razon1'],$_POST['domicilio1'], $_POST['nroFactura1'],$_POST['importeFacturado1'], $_POST['opcion1']);
        } elseif (isset($_POST['btn_cuadro_retenciones2'])) {
            cuadroRetenciones($_POST['nroVale2'], $_POST['cuit2'], $_POST['razon2'],$_POST['domicilio2'], $_POST['nroFactura2'],$_POST['importeFacturado2'], $_POST['opcion2']);
        } elseif (isset($_POST['btn_cuadro_retenciones3'])) {
            cuadroRetenciones($_POST['nroVale3'], $_POST['cuit3'], $_POST['razon3'],$_POST['domicilio3'], $_POST['nroFactura3'],$_POST['importeFacturado3'], $_POST['opcion3']);
        } elseif (isset($_POST['btn_cuadro_retenciones4'])) {
            cuadroRetenciones($_POST['nroVale4'], $_POST['cuit4'], $_POST['razon4'],$_POST['domicilio4'], $_POST['nroFactura4'],$_POST['importeFacturado4'], $_POST['opcion4']);
        } elseif (isset($_POST['btn_cuadro_retenciones5'])) {
            cuadroRetenciones($_POST['nroVale5'], $_POST['cuit5'], $_POST['razon5'],$_POST['domicilio5'], $_POST['nroFactura5'],$_POST['importeFacturado5'], $_POST['opcion5']);
        } elseif (isset($_POST['btn_cuadro_retenciones6'])) {
            cuadroRetenciones($_POST['nroVale6'], $_POST['cuit6'], $_POST['razon6'],$_POST['domicilio6'], $_POST['nroFactura6'],$_POST['importeFacturado6'], $_POST['opcion6']);
        } elseif (isset($_POST['btn_cuadro_retenciones7'])) {
            cuadroRetenciones($_POST['nroVale7'], $_POST['cuit7'], $_POST['razon7'],$_POST['domicilio7'], $_POST['nroFactura7'],$_POST['importeFacturado7'], $_POST['opcion7']);
        } elseif (isset($_POST['btn_cuadro_retenciones8'])) {
            cuadroRetenciones($_POST['nroVale8'], $_POST['cuit8'], $_POST['razon8'],$_POST['domicilio8'], $_POST['nroFactura8'],$_POST['importeFacturado8'], $_POST['opcion8']);
        }

        
        elseif (isset($_POST['btn_texto_gde'])) {
            textoGDE($_POST['nroVale1'],$_POST['razon1'],$_POST['importeFacturado1'],$_POST['opcion1'],
                     $_POST['nroVale2'],$_POST['razon2'],$_POST['importeFacturado2'],$_POST['opcion2'],
                     $_POST['nroVale3'],$_POST['razon3'],$_POST['importeFacturado3'],$_POST['opcion3'],
                     $_POST['nroVale4'],$_POST['razon4'],$_POST['importeFacturado4'],$_POST['opcion4'],
                     $_POST['nroVale5'],$_POST['razon5'],$_POST['importeFacturado5'],$_POST['opcion5'],
                     $_POST['nroVale6'],$_POST['razon6'],$_POST['importeFacturado6'],$_POST['opcion6'],
                     $_POST['nroVale7'],$_POST['razon7'],$_POST['importeFacturado7'],$_POST['opcion7'],
                     $_POST['nroVale8'],$_POST['razon8'],$_POST['importeFacturado8'],$_POST['opcion8']);
        }
         elseif (isset($_POST['btn_texto_moi'])) {
            textoMOI($_POST['fechaTransferencia'],$_POST['notaGDE'], $_POST['nroVale1'], $_POST['nroTransferencia1'],$_POST['importeFacturado1'],$_POST['opcion1'],
                                                                      $_POST['nroVale2'], $_POST['nroTransferencia2'],$_POST['importeFacturado2'],$_POST['opcion2'],
                                                                      $_POST['nroVale3'], $_POST['nroTransferencia3'],$_POST['importeFacturado3'],$_POST['opcion3'],
                                                                      $_POST['nroVale4'], $_POST['nroTransferencia4'],$_POST['importeFacturado4'],$_POST['opcion4'],
                                                                      $_POST['nroVale5'], $_POST['nroTransferencia5'],$_POST['importeFacturado5'],$_POST['opcion5'],
                                                                      $_POST['nroVale6'], $_POST['nroTransferencia6'],$_POST['importeFacturado6'],$_POST['opcion6'],
                                                                      $_POST['nroVale7'], $_POST['nroTransferencia7'],$_POST['importeFacturado7'],$_POST['opcion7'],
                                                                      $_POST['nroVale8'], $_POST['nroTransferencia8'],$_POST['importeFacturado8'],$_POST['opcion8']);
        }

    }

    function textoTransferencia($nroVale, $nroTransferencia, $razon, $importeFacturado, $opcion){


        //Calculo de Importe retenido
        if ($opcion === 'SERVICIOS') {
                $resultado = ($importeFacturado*(21/121))*0.8;
            } elseif ($opcion === 'BIENES') {
                $resultado = ($importeFacturado * (21/121))*0.5;
            } else {
                $resultado = 0; // Opción inválida, puedes manejarlo según tu necesidad
            }

        $resultadoSUSS = ($importeFacturado/1.21)*0.01;

        if($resultado<400){
            $resultado=0;
        }
        if($resultadoSUSS<400){
            $resultadoSUSS=0;
        }

            $sumaRetenciones = $resultado + $resultadoSUSS;
            $importeNeto=$importeFacturado-$sumaRetenciones;


            //Formateo a Moneda
            $importeNetoFormated=number_format($importeNeto, 2, ',', '.');
            $resultadoSUSSFormated=number_format($resultadoSUSS, 2, ',', '.');
            $resultadoFormated = number_format($resultado, 2, ',', '.');
            $importeFacturadoFormated=number_format($importeFacturado, 2, ',', '.');
            $sumaRetencionesFormated = number_format($sumaRetenciones, 2, ',', '.');


            $formatter = new NumeroALetras();

            //Creo Cadena de TEXTO y le agrego "PESOS"
            $sumaRetencionesLetras=$formatter->toInvoice($sumaRetenciones, 2, '');
            $sumaRetencionesLetras='PESOS' . ' ' .$sumaRetencionesLetras;

            $resultadoLetras=$formatter->toInvoice($resultado, 2, '');
            $resultadoLetras='PESOS' . ' ' .$resultadoLetras;

            $resultadoSUSSLetras=$formatter->toInvoice($resultadoSUSS, 2, '');
            $resultadoSUSSLetras='PESOS' . ' ' .$resultadoSUSSLetras;
    

        if($resultadoSUSS!= 0){
            $TEXTO=' CORRESPONDIENTE A IVA '.$resultadoLetras.' ($ '.$resultadoFormated.') Y A SUSS '.$resultadoSUSSLetras.' ($ '.$resultadoSUSSFormated.').';
        }

        $dompdf = new Dompdf();

            $html = <<<HTML
            
            <!DOCTYPE html>
            <html>
            <title>Texto Transferencia</title>
            <style>
                body {
                    padding-left: 40px;
                    padding-top: 660px;
                    font-family: Arial, Helvetica, sans-serif;        
                }
                p {
                    text-align: justify;
                }
                h2{
                    text-decoration: underline;
                    font-size: 19px;
                }
            </style>
            <br>PREFECTURA NAVAL ARGENTINA<br>PREFECTURA DEL COMAHUE<br>SECCION ADMINISTRATIVO FINANCIERA<br><h2>MOTIVO TRANSFERENCIA:</h2>
            <p>EFECTUÓ TRANSACCIÓN BNA Nº <strong>$nroTransferencia CTA. 2689/43 CARGOS PERSONAL</strong>, LA SUMA DE
                $sumaRetencionesLetras ($ $sumaRetencionesFormated), RETENCION $razon (VALE Nº $nroVale - ANT GTS VARIOS).$TEXTO</p>
            
            </html>
            HTML;
    
        $dompdf->loadHtml($html);

        $dompdf->setPaper('A4', 'portrait');
        
        $dompdf->render();
        
        $dompdf->stream($razon.'.pdf', ['Attachment' => false]);
    }    

    function cuadroRetenciones ($nroVale, $cuit,$razon,$domicilio,$nroFactura,$importeFacturado,$opcion){
        
        $domicilio = strtoupper($domicilio);
        $primerosDosDigitos = substr($cuit, 0, 2);
        $siguientesOchoDigitos = substr($cuit, 2, 8);
        $ultimoDigito = substr($cuit, -1);
        $cuilFormated = $primerosDosDigitos . '-' . $siguientesOchoDigitos . '-' . $ultimoDigito;

        if ($opcion === 'SERVICIOS') {
                $resultado = ($importeFacturado*(21/121))*0.8;
            } elseif ($opcion === 'BIENES') {
                $resultado = ($importeFacturado * (21/121))*0.5;
            } else {
                $resultado = 0;
            }

        $resultadoSUSS = ($importeFacturado/1.21)*0.01;

        if($resultado<400){
            $resultado=0;
        }
        if($resultadoSUSS<400){
            $resultadoSUSS=0;
        }

        $sumaRetenciones = $resultado + $resultadoSUSS;
        $importeNeto=$importeFacturado-$sumaRetenciones;

        $importeNetoFormated=number_format($importeNeto, 2, ',', '.');
        $resultadoSUSSFormated=number_format($resultadoSUSS, 2, ',', '.');
        $resultadoFormated = number_format($resultado, 2, ',', '.');
        $importeFacturadoFormated=number_format($importeFacturado, 2, ',', '.');
        $sumaRetencionesFormated = number_format($sumaRetenciones, 2, ',', '.');

        $formatter = new NumeroALetras();

        $sumaRetencionesLetras=$formatter->toInvoice($sumaRetenciones, 2, '');
        $sumaRetencionesLetras='PESOS' . ' ' .$sumaRetencionesLetras;

        $resultadoLetras=$formatter->toInvoice($resultado, 2, '');
        $resultadoLetras='PESOS' . ' ' .$resultadoLetras;

        $resultadoSUSSLetras=$formatter->toInvoice($resultadoSUSS, 2, '');
        $resultadoSUSSLetras='PESOS' . ' ' .$resultadoSUSSLetras;

        $dompdf = new Dompdf();
        
        $html = <<<HTML
        
        <!DOCTYPE html>
            <html>
                            <head>
                            <title>Retenciones</title>
                                    <style>
                                    body {
                                        display: flex;
                                        justify-content: center;
                                        align-items: center;
                                        min-height: 100vh;
                                        margin: 0;
                                        padding-left: 50px;
                                        font-family: Arial, Helvetica, sans-serif;
                                        color: black;
                                    }
                                    h1 {
                                        
                                        font-size: 16px;
                                        font-family: Arial, Helvetica, sans-serif;
                                        margin: 0;
                                        padding: 0;
                                    }

                                    h2 {
                                        text-align: center;
                                        text-decoration: underline;
                                        font-size: 20px;
                                    }
                                    h3 {
                                    text-align: left;
                                    font-size: 16px;
                                    }
                                    table {
                                        border-collapse: collapse;
                                    }
                                    td {
                                        border: none;
                                    }
                                    td.center {
                                        text-align: center;
                                        font-size: 14px;
                                    }
                                    td.right {
                                        text-align: right;
                                        font-size: 18px;
                                        vertical-align: middle;
                                        border: 2px solid black;
                                    }
                                    td.highlighted {
                                        border: 2px solid black;
                                        vertical-align: middle;
                                        font-size: 18px;
                                        text-align: center;
                                    }
                                    td.cell1 {
                                        text-align: right;
                                        font-size: 14px;
                                    }
                                    td.cell2-1 {
                                        text-align: left;
                                        font-size: 14px;
                                    }
                                    td.cell2-2 {
                                        text-align: right;
                                        font-size: 14px;
                                        
                                    }
                                    td.cell2-3 {
                                        text-align: left;
                                        border-left: 2px solid black;
                                        border-bottom: 2px solid black;
                                        border-top: 2px solid black;
                                        font-size: 14px;
                                    }
                                    td.cell2-4 {
                                        text-align: right;
                                        font-size: 14px;
                                        border-bottom: 2px solid black;
                                        border-top: 2px solid black;
                                        border-right: 2px solid black; 


                                    }      
                                    #tabla2 {
                                        display: flex;
                                        justify-content: center;
                                        margin-top: 20px; 

                                    }
                                    #tabla2 table {
                                        width: 200px; 
                                        margin: 0 auto;
                                    }
                                </style>
                                </head>
                            <body>
                            <h1>PREFECTURA NAVAL ARGENTINA</h1>
                            <h1>PREFECTURA NACIONAL</h1>
                            <br><br>
                            

                            <div>
                                <h2>CUADRO DE RETENCIONES</h2>
                                <table >
                                    <tbody>
                                        <tr>
                                            <td width="120" height="28"><b>Nº DE OFICIO:</td>
                                            <td width="120" height="28"><b>VALE Nº $nroVale</td>
                                            <td width="60" height="25">&nbsp;</td>
                                            <td width="100" height="25">&nbsp;</td>
                                        </tr>
                                        <tr>
                                        <td height="28"><b>RAZÓN SOCIAL:</td>
                                        <td colspan="3" height="25">$razon</td>
                                        </tr>
                                        <tr>
                                        <td height="28"><b>C.U.I.T.:</td>
                                        <td height="28">$cuilFormated</td>
                                        </tr>
                                        <tr>
                                        <td height="28"><b>DOMICILIO:</td>
                                        <td colspan="3" height="28">$domicilio</td>
                                        </tr>
                                        <tr>
                                        <td height="28"><b>FACTURA: </td>
                                        <td height="28">$nroFactura</td>
                                        </tr>
                                        <tr>
                                        <td height="28"><b>IMPORTE:</td>
                                        <td height="28" class="right">$ $importeFacturadoFormated</td>
                                        <td height="28"></td>
                                        <td height="28" class="highlighted">$opcion</td>
                                        </tr>
                                    </tbody>
                                    </table>
                                </div>
                            
                                <br><br>    

                                <div id="tabla2">
                                <h3>RETENCIÓN APLICADA:</h2>
                                <table>
                                    <tbody>
                                        <tr>
                                            <td width="130" class="cell2-1" height="12">GANANCIAS</td>
                                            <td width="70"  class="cell2-2" height="12">$ 0,00</td>
                                        </tr>
                                        <tr>
                                            <td class="cell2-1" height="12">IVA</td>
                                            <td class="cell2-2" height="12">$ $resultadoFormated</td>
                                        
                                        </tr>
                                        <tr>
                                            <td class="cell2-1" height="12">S.U.S.S.</td>
                                            <td class="cell2-2" height="12">$ $resultadoSUSSFormated</td>
                                        
                                        </tr>
                                        <tr>
                                            <td class="cell2-3" height="12">TOTAL RETENCIONES</td>
                                            <td class="cell2-4" height="12">$ $sumaRetencionesFormated</td>
                                        
                                        </tr>
                                        <tr>
                                            <td class="cell2-1" height="12"></td>
                                            <td class="cell2-2" height="12"></td>
                                        
                                        </tr>
                                        <tr>
                                            <td class="cell2-3" height="12">IMPORTE NETO</td>
                                            <td class="cell2-4" height="12">$ $importeNetoFormated</td>
                                            
                                        </tr>      
                                        </tbody>
                                    </table>
                                </div>
                            </body>
                </html>

    
    
        HTML;
        
        $dompdf->loadHtml($html);
        
        $dompdf->setPaper('A4', 'portrait');

        $dompdf->render(); 

        $dompdf->stream($razon.'.pdf', ['Attachment' => false]);
        
    }   

    function textoGDE($nroVale1, $razon1, $importeFacturado1, $opcion1,
                    $nroVale2, $razon2, $importeFacturado2, $opcion2,
                    $nroVale3, $razon3, $importeFacturado3, $opcion3,
                    $nroVale4, $razon4, $importeFacturado4, $opcion4,
                    $nroVale5, $razon5, $importeFacturado5, $opcion5,
                    $nroVale6, $razon6, $importeFacturado6, $opcion6,
                    $nroVale7, $razon7, $importeFacturado7, $opcion7,
                    $nroVale8, $razon8, $importeFacturado8, $opcion8){



        $detenerBucle = false;

        for ($i = 1; $i <= 8 && !$detenerBucle; $i++) {

                    $nroVale = ${'nroVale' . $i};
                    $razon = ${'razon' . $i};
                    $importeFacturado = ${'importeFacturado' . $i};
                    $opcion = ${'opcion' . $i};

                    //Recupero el Valor de OPCION, en base a eso calculo la RETENCION
                    if ($opcion === 'SERVICIOS') {
                        $resultado = ($importeFacturado*(21/121))*0.8;
                    } elseif ($opcion === 'BIENES') {
                        $resultado = ($importeFacturado * (21/121))*0.5;
                    } else {
                        $resultado = 0;
                    }

                    //Calculando SUSS
                    $resultadoSUSS = ($importeFacturado/1.21)*0.01;
                    //Calculo el minimo
                    if($resultadoSUSS<400){
                        $resultadoSUSS=0;
                    }

                    //Formateo a moneda
                    $resultadoSUSSFormated=number_format($resultadoSUSS, 2, ',', '.');
                    $resultadoFormated = number_format($resultado, 2, ',', '.');
           
                    if($resultadoSUSS != 0 ){
                        $test='<tr><td style="text-align: center;" rowspan="2">'.$razon.'</td> 
                                   <td style="text-align: center;" >$ '.$resultadoFormated.'</td> 
                                   <td style="text-align: center;" >IVA</td> 
                                   <td style="text-align: center;" rowspan="2">'.$nroVale.'</td> </tr>                       
                       <tr><td style="text-align: center;">$ '.$resultadoSUSSFormated.'</td>
                       <td style="text-align: center;" >SUSS</td></tr>';

                        $totalRetencion = $totalRetencion + ($resultadoSUSS + $resultado);
                    }

                    else{
                        $test='<tr><td style="text-align: center;">'.$razon.'</td> 
                       <td style="text-align: center;">$ '.$resultadoFormated.'</td> 
                       <td style="text-align: center;">IVA</td> 
                       <td style="text-align: center;">'.$nroVale.'</td> </tr>';

                        $totalRetencion = $totalRetencion + $resultado;
                    }
                    
                    $textoCompleto = $textoCompleto . $test;
                    

                    $y=$i+1;
                    $nroVale2 = ${'nroVale' . $y};
                    if (empty($nroVale2)) {
                    $detenerBucle = true;
                    }       
            }
            $totalRetencionFormated=number_format($totalRetencion, 2, ',', '.');

            $html = <<<HTML
                <!DOCTYPE html>
                <html>
                <title>Texto GDE</title>
                <!--COPIA DESDE ACA-->
                <div style="text-align: justify;"><span style="font-size:14px"><span style="font-family:times new roman,times,serif">PREFECTURA NAVAL ARGENTINA </span></span></div>
                <div style="text-align: justify;"><span style="font-size:14px"><span style="font-family:times new roman,times,serif">Autoridad Marítima</span></span></div>
                <div style="text-align: justify;"> </div>
                <div style="text-align: justify;"> </div>
                <div style="text-align: justify;"> </div>
                <div style="text-align: justify;"><span style="font-size:14px"><span style="font-family:times new roman,times,serif">AL SEÑOR PREFECTO DE ZONA LACUSTRE Y DEL COMAHUE (División Administrativo Financiera):</span></span></div>
                <div style="text-align: justify;"> </div>
                <div style="text-align: justify;"><span style="font-size:14px"><span style="font-family:times new roman,times,serif">Con motivo de las retenciones practicadas, elevo el presente solicitando la emisión de los respectivos certificados de retenciones (AFIP-SI.CO.RE), acorde a los datos que se detallan en el ANEXO I adjunto como archivo embebido.</span></span></div>
                <div style="text-align: justify;"> </div>
                <div style="text-align: justify;"> </div>
                
                    <table border="1" cellpadding="1" cellspacing="1" style="width:600px; margin: 0 auto;">
                        <tbody>
                            <tr>
                                <td style="text-align: center;"><strong>EMPRESA</strong></td>
                                <td style="text-align: center;"><strong>IMPORTE</strong></td>
                                <td style="text-align: center;"><strong>CONCEPTO</strong></td>
                                <td style="text-align: center;"><strong>VALE/RESP</strong></td>
                            </tr>

                            $textoCompleto
                            
                        </tbody>
                    </table>
                <div style="text-align: justify;"> </div>
                <div style="text-align: justify;"> </div>
                <div style="text-align: justify;"><span style="font-size:14px"><span style="font-family:times new roman,times,serif">La presente solicitud de Certificados de Retenciones asciende a la suma total de PESOS ($ $totalRetencionFormated).</span></span></div>
                <!--HASTA ACA-->

                <br><br><br>

                </html>

            HTML;
        

        $html2 = str_replace('<!DOCTYPE html>', '', $html);
        $html2 = str_replace('<html>', '', $html2);
        $html2 = str_replace('<title>Texto GDE</title>', '', $html2);
        $html2 = str_replace('<!--COPIA DESDE ACA-->', '', $html2);

        $html2 = str_replace('<!--HASTA ACA-->', '', $html2);
        $html2 = str_replace('<br><br><br>', '', $html2);
        $html2 = str_replace('</html>', '', $html2);



        $user_html = htmlspecialchars($html2);



        $dompdf = new Dompdf();

        $combined_html = $html .'COPIAR Y PEGAR EN EL GDE: <br><br>'. $user_html;

        $dompdf->loadHtml($combined_html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream($razon.'.pdf', ['Attachment' => false]);
        
    }

    function textoMOI( $fechaTransferencia,$notaGDE, $nroVale1, $nroTransferencia1, $importeFacturado1, $opcion1, 
                                                      $nroVale2, $nroTransferencia2, $importeFacturado2, $opcion2,
                                                      $nroVale3, $nroTransferencia3, $importeFacturado3, $opcion3, 
                                                      $nroVale4, $nroTransferencia4, $importeFacturado4, $opcion4,
                                                      $nroVale5, $nroTransferencia5, $importeFacturado5, $opcion5,
                                                      $nroVale6, $nroTransferencia6, $importeFacturado6, $opcion6, 
                                                      $nroVale7, $nroTransferencia7, $importeFacturado7, $opcion7, 
                                                      $nroVale8, $nroTransferencia8, $importeFacturado8, $opcion8){


        
        if ($opcion1 === 'SERVICIOS') {
                $resultado = ($importeFacturado1*(21/121))*0.8;
            } elseif ($opcion1 === 'BIENES') {
                $resultado = ($importeFacturado1*(21/121))*0.5;
            } else {
                $resultado = 0;
            }

        $resultadoSUSS = ($importeFacturado1/1.21)*0.01;

        if($resultado<400){
            $resultado=0;
        }
        if($resultadoSUSS<400){
            $resultadoSUSS=0;
        }
        $sumaRetenciones = $resultado + $resultadoSUSS;

        $sumaRetencionesFormated = number_format($sumaRetenciones, 2, ',', '.');
        $resultadoFormated = number_format($resultado, 2, ',', '.');
        $resultadoSUSSFormated = number_format($resultadoSUSS, 2, ',', '.');

        $detenerBucle = false;

        


        //GRUPO FECHA Y HORA
        $fechaHoy = date("d");
        $horaCompleta = date("H:i:s");
        $nuevaHoraCompleta = date("H:i:s", strtotime($horaCompleta) +  (90 * 60));        
        $horaMinutos = $fechaHoy. substr($nuevaHoraCompleta, 0, 2) . substr($nuevaHoraCompleta, 3, 1).'8';
        $mesActual = date("M");
        $mesAbreviado = strtoupper($mesActual);
        $anoCompleto = date("Y");

        //CAMBIAR FORMATO DE FECHA
        setlocale(LC_TIME, 'es_ES.UTF-8');
        $fechaFormateada = strftime("%d DE %B DE %Y", strtotime($fechaTransferencia));
        $fechaFormateada = strtoupper($fechaFormateada);

        
        //BUCLE PARA LLENAR ARRAY
            function calcularRetenciones($opcion, $importeFacturado, $nroTransferencia, $nroVale)
            {
                 if (empty($nroVale)) {
                    return ''; 
                }                
                if ($opcion === 'SERVICIOS') {
                    $resultado = ($importeFacturado * (21 / 121)) * 0.8;
                } elseif ($opcion === 'BIENES') {
                    $resultado = ($importeFacturado * (21 / 121)) * 0.5;
                } else {
                    $resultado = 0;
                }

                $resultadoSUSS = ($importeFacturado / 1.21) * 0.01;

                if ($resultado < 400) {
                    $resultado = 0;
                }
                if ($resultadoSUSS < 400) {
                    $resultadoSUSS = 0;
                }
                $sumaRetenciones = $resultado + $resultadoSUSS;

                $sumaRetencionesFormated = number_format($sumaRetenciones, 2, ',', '.');
                $resultadoFormated = number_format($resultado, 2, ',', '.');
                $resultadoSUSSFormated = number_format($resultadoSUSS, 2, ',', '.');

                $transferencia = '-TRANSFERENCIA Nº ' . $nroTransferencia . ' $ ' . $sumaRetencionesFormated . ' - VALE Nº ' . $nroVale . ' - IVA $ ' . $resultadoFormated;
                if ($resultadoSUSS != 0) {
                    $transferencia .= ' - SUSS $ ' . $resultadoSUSSFormated . '.-';
                } else {
                    $transferencia .= '.-';
                }

                return $transferencia;
            }

            $transferencias = array();

            $transferencias[] = calcularRetenciones($opcion1, $importeFacturado1, $nroTransferencia1, $nroVale1);
            $transferencias[] = calcularRetenciones($opcion2, $importeFacturado2, $nroTransferencia2, $nroVale2);
            $transferencias[] = calcularRetenciones($opcion3, $importeFacturado3, $nroTransferencia3, $nroVale3);
            $transferencias[] = calcularRetenciones($opcion4, $importeFacturado4, $nroTransferencia4, $nroVale4);
            $transferencias[] = calcularRetenciones($opcion5, $importeFacturado5, $nroTransferencia5, $nroVale5);
            $transferencias[] = calcularRetenciones($opcion6, $importeFacturado6, $nroTransferencia6, $nroVale6);
            $transferencias[] = calcularRetenciones($opcion7, $importeFacturado7, $nroTransferencia7, $nroVale7);
            $transferencias[] = calcularRetenciones($opcion8, $importeFacturado8, $nroTransferencia8, $nroVale8);

                     
            $textoMOI = '';
            foreach ($transferencias as $transferencia) {
                if (!empty($transferencia)) {
                    $textoMOI .= "<br>" . $transferencia;
                }
            }



        //Texto MOI
            $html = <<<HTML
                <!DOCTYPE html>
                <html>
                    <head>
                        <title>Mensaje Oficial Interno</title>
                        <style>
                        p {
                            margin: 0; /* Eliminar el margen vertical predeterminado */
                            line-height: 1.2; /* Ajustar el interlineado al mínimo */
                            font-family:times new roman,times,serif;
                            text-align: justify;
                        }
                        .underline {
                            text-decoration: underline;
                        }

                    </style>
                    </head>
                    <body>
                        <p>Nº GFH: $horaMinutos/$mesAbreviado/$anoCompleto</p>
                        <p>CAT: "P"</p>
                        <p>FM: CHUE</p>
                        <p>TO: DAFI</p>
                        <p>INFO: DSUR- PZLC</p>
                        <br>
                        <p>BT</p>
                        <p class="underline"><strong>PARA DIVISION TESORERIA Y DIVISION CONTADURIA CENTRAL:</strong></p>
                        <p >INFORMO FECHA $fechaFormateada TRANSFERENCIA EFECTUADA BANCO DE LA NACION ARGENTINA X CTA. 2689/43 "CARGOS AL PERSONAL" X CONCEPTO RETENCIONES PRACTICADAS X ACORDE SIGUIENTE DETALLE:</p>
                        <p> $textoMOI </p>
                        <br>
                        <p>ELEVADA SOLICITUD CERTIFICADOS DE RETENCION ORIGINAL POR NOTA GDE CCOO $notaGDE.</p>
                        <p>BT</p>
                    </body>
                </html>
                HTML;

        $dompdf = new Dompdf();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream($razon.'.pdf', ['Attachment' => false]);
        
    }
?>