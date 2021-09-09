<?php
namespace App\Service\Generator;

use App\Entity\Captcha;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Project:  Securimage: A PHP class dealing with CAPTCHA images, audio, and validation
 * File:     securimage.php
 *
 * Copyright (c) 2018, Drew Phillips
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 *  - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *  - Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * Any modifications to the library should be indicated clearly in the source code
 * to inform users that the changes are not a part of the original software.
 *
 * @link https://www.phpcaptcha.org Securimage Homepage
 * @link https://www.phpcaptcha.org/latest.zip Download Latest Version
 * @link https://github.com/dapphp/securimage GitHub page
 * @link https://www.phpcaptcha.org/Securimage_Docs/ Online Documentation
 * @copyright 2018 Drew Phillips
 * @author Drew Phillips <drew@drew-phillips.com>
 * @version 3.6.8 (May 2020)
 * @package Securimage
 *
 */

/**
 * Securimage CAPTCHA Class.
 *
 * A class for creating and validating secure CAPTCHA images and audio.
 *
 * The class contains many options regarding appearance, security, storage of
 * captcha data and image/audio generation options.
 *
 * @package    Securimage
 * @subpackage classes
 * @author     Drew Phillips <drew@drew-phillips.com>
 *
 */
class Securimage
{
    /*%*********************************************************************%*/
    // Properties

    /**
     * The width of the captcha image
     * @var int
     */
    public $image_width = 460;

    /**
     * The height of the captcha image
     * @var int
     */
    public $image_height = 160;

    /**
     * Font size is calculated by image height and this ratio.  Leave blank for
     * default ratio of 0.4.
     *
     * Valid range: 0.1 - 0.99.
     *
     * Depending on image_width, values > 0.6 are probably too large and
     * values < 0.3 are too small.
     *
     * @var float
     */
    public $font_ratio;

    /**
     * The background color of the captcha
     * @var Colorize|string
     */
    public $image_bg_color = '#fff';

    /**
     * The color of the captcha text
     * @var Colorize|string
     */
    public $text_color     = '#7678ac';

    /**
     * The color of the lines over the captcha
     * @var Colorize|string
     */
    public $line_color     = '#7678ac';

    /**
     * The color of the noise that is drawn
     * @var Colorize|string
     */
    public $noise_color    = '#7678ac';

    /**
     * How transparent to make the text.
     *
     * 0 = completely opaque, 100 = invisible
     *
     * @var int
     */
    public $text_transparency_percentage = 20;

    /**
     * Whether or not to draw the text transparently.
     *
     * true = use transparency, false = no transparency
     *
     * @var bool
     */
    public $use_transparent_text         = true;

    /**
     * The length of the captcha code
     * @var int
     */
    public $code_length    = 4;

    /**
     * Display random spaces in the captcha text on the image
     *
     * @var bool true to insert random spacing between groups of letters
     */
    public $use_random_spaces  = false;

    /**
     * Draw each character at an angle with random starting angle and increase/decrease per character
     * @var bool true to use random angles, false to draw each character normally
     */
    public $use_text_angles = false;

    /**
     * Instead of centering text vertically in the image, the baseline of each character is
     * randomized in such a way that the next character is drawn slightly higher or lower than
     * the previous in a step-like fashion.
     *
     * @var bool true to use random baselines, false to center text in image
     */
    public $use_random_baseline = false;

    /**
     * Draw a bounding box around some characters at random.  20% of the time, random boxes
     * may be drawn around 0 or more characters on the image.
     *
     * @var bool  true to randomly draw boxes around letters, false not to
     */
    public $use_random_boxes = false;

    /**
     * The character set to use for generating the captcha code
     * @var string
     */
    public $charset        = 'abcdefgjkmpqrstuvwxyz';

    /**
     * How long in seconds a captcha remains valid, after this time it will be
     * considered incorrect.
     *
     * @var int
     */
    public $expiry_time    = 900;

    /**
     * The level of distortion.
     *
     * 0.75 = normal, 1.0 = very high distortion
     *
     * @var double
     */
    public $perturbation = 0.85;

    /**
     * How many lines to draw over the captcha code to increase security
     * @var int
     */
    public $num_lines    = 5;

    /**
     * The level of noise (random dots) to place on the image, 0-10
     * @var int
     */
    public $noise_level  = 2;

    /**
     * The signature text to draw on the bottom corner of the image
     * @var string
     */
    public $image_signature = '';

    /**
     * The color of the signature text
     * @var Securimage_Color|string
     */
    public $signature_color = '#707070';

    /**
     * The path to the ttf font file to use for the signature text.
     * Defaults to $ttf_file (AHGBold.ttf)
     *
     * @see Securimage::$ttf_file
     * @var string
     */
    public $signature_font;

    /**
     * The TTF font file to use to draw the captcha code.
     *
     * Leave blank for default font AHGBold.ttf
     *
     * @var string
     */
    public $ttf_file;

    /**
     * The GD image resource of the captcha image
     *
     * @var resource
     */
    protected $im;

    /**
     * A temporary GD image resource of the captcha image for distortion
     *
     * @var resource
     */
    protected $tmpimg;

    /**
     * The background image GD resource
     * @var string
     */
    protected $bgimg;

    /**
     * Scale factor for magnification of distorted captcha image
     *
     * @var int
     */
    protected $iscale = 2;

    /**
     * Absolute path to securimage directory.
     *
     * This is calculated at runtime
     *
     * @var string
     */
    public $securimage_path = null;

    /**
     * The captcha challenge value.
     *
     * Either the case-sensitive/insensitive word captcha, or the solution to
     * the math captcha.
     *
     * @var string|bool Captcha challenge value
     */
    protected $code;

    /**
     * The GD color for the background color
     *
     * @var int
     */
    protected $gdbgcolor;

    /**
     * The GD color for the text color
     *
     * @var int
     */
    protected $gdtextcolor;

    /**
     * The GD color for the line color
     *
     * @var int
     */
    protected $gdlinecolor;

    /**
     * The GD color for the signature text color
     *
     * @var int
     */
    protected $gdsignaturecolor;

    /**
     * @var EntityManagerInterface $em
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        if (function_exists('mb_internal_encoding')) {
            mb_internal_encoding('UTF-8');
        }

        $this->image_bg_color  = $this->initColor($this->image_bg_color,  '#ffffff');
        $this->text_color      = $this->initColor($this->text_color,      '#616161');
        $this->line_color      = $this->initColor($this->line_color,      '#616161');
        $this->noise_color     = $this->initColor($this->noise_color,     '#616161');
        $this->signature_color = $this->initColor($this->signature_color, '#616161');

        $this->ttf_file = $_SERVER['DOCUMENT_ROOT'] . '/AHGBold.ttf';
        $this->signature_font = $this->ttf_file;

        $this->em = $em;
    }

    /**
     * This method generates a new captcha code.
     *
     * Generates a random captcha code based on *charset*, math problem, or captcha from the wordlist and saves the value to the session and/or database.
     */
    public function createCode()
    {
        $expire = new \DateTime();
        $expire->modify("+" . $this->expiry_time . " seconds");

        $captcha = new Captcha();
        $captcha->setCode($this->generateCode());
        $captcha->setExpireTimestamp($expire);

        $this->em->persist($captcha);
        $this->em->flush();

        return [
            'id' => $captcha->getId(),
            'captcha' => $this->siteURL() . "captcha/" . $captcha->getId()
        ];
    }

    public function isValid(?string $captchaId, ?string $code)
    {
        $captcha = $this->em->getRepository(Captcha::class)->findOneBy([
            'id' => $captchaId,
            'consumed' => true,
            'code' => strtolower($code),
        ]);

        if(!$captcha INSTANCEOF Captcha){
            return false;
        }

        $timestamp = new \DateTime();
        if($timestamp > $captcha->getExpireTimestamp()){
            return false;
        }

        return true;
    }

    /**
     * @param string $captchaId
     * @throws \Exception
     */
    public function show(string $captchaId)
    {
        $captcha = $this->em->getRepository(Captcha::class)->findOneBy([
            'id' => $captchaId,
            'consumed' => false,
        ]);

        if(!$captcha INSTANCEOF Captcha){
            throw new NotFoundHttpException("Captcha not found.");
        }

        $captcha->setConsumed(true);
        $this->em->flush();

        $this->doImage($captcha);
    }

    /**
     * The main image drawing routing, responsible for constructing the entire image and serving it
     */
    protected function doImage(Captcha $captcha)
    {
        $this->im = imagecreatetruecolor($this->image_width, $this->image_height);

        if (function_exists('imageantialias')) {
            imageantialias($this->im, true);
        }

        $this->allocateColors();

        if ($this->perturbation > 0) {
            $this->tmpimg = imagecreatetruecolor($this->image_width * $this->iscale, $this->image_height * $this->iscale);
            imagepalettecopy($this->tmpimg, $this->im);
        } else {
            $this->iscale = 1;
        }

        $this->setBackground();

        if ($this->noise_level > 0) {
            $this->drawNoise();
        }

        $this->drawCode($captcha->getCode());

        if ($this->perturbation > 0 && is_readable($this->ttf_file)) {
            $this->distortedCopy();
        }

        if ($this->num_lines > 0) {
            $this->drawLines();
        }

        if (trim($this->image_signature) != '') {
            $this->addSignature();
        }

        $this->output();
    }

    /**
     * Allocate the colors to be used for the image
     */
    protected function allocateColors()
    {
        $this->gdbgcolor = imagecolorallocate($this->im,
            $this->image_bg_color->r,
            $this->image_bg_color->g,
            $this->image_bg_color->b);

        $alpha = intval($this->text_transparency_percentage / 100 * 127);

        if ($this->use_transparent_text == true) {
            $this->gdtextcolor = imagecolorallocatealpha($this->im,
                $this->text_color->r,
                $this->text_color->g,
                $this->text_color->b,
                $alpha);
            $this->gdlinecolor = imagecolorallocatealpha($this->im,
                $this->line_color->r,
                $this->line_color->g,
                $this->line_color->b,
                $alpha);
        } else {
            $this->gdtextcolor = imagecolorallocate($this->im,
                $this->text_color->r,
                $this->text_color->g,
                $this->text_color->b);
            $this->gdlinecolor = imagecolorallocate($this->im,
                $this->line_color->r,
                $this->line_color->g,
                $this->line_color->b);
        }

        $this->gdsignaturecolor = imagecolorallocate($this->im,
            $this->signature_color->r,
            $this->signature_color->g,
            $this->signature_color->b);
    }

    /**
     * The the background color, or background image to be used
     */
    protected function setBackground()
    {
        // set background color of image by drawing a rectangle since imagecreatetruecolor doesn't set a bg color
        imagefilledrectangle($this->im, 0, 0,
            $this->image_width, $this->image_height,
            $this->gdbgcolor);

        if ($this->perturbation > 0) {
            imagefilledrectangle($this->tmpimg, 0, 0,
                $this->image_width * $this->iscale, $this->image_height * $this->iscale,
                $this->gdbgcolor);
        }

        if ($this->bgimg == '') {
            return;
        }

        $dat = @getimagesize($this->bgimg);
        if($dat == false) {
            return;
        }

        switch($dat[2]) {
            case 1:  $newim = @imagecreatefromgif($this->bgimg); break;
            case 2:  $newim = @imagecreatefromjpeg($this->bgimg); break;
            case 3:  $newim = @imagecreatefrompng($this->bgimg); break;
            default: return;
        }

        if(!$newim) return;

        imagecopyresized($this->im, $newim, 0, 0, 0, 0,
            $this->image_width, $this->image_height,
            imagesx($newim), imagesy($newim));
    }

    /**
     * Draws the captcha code on the image
     */
    protected function drawCode(string $captcha_text)
    {
        $ratio = ($this->font_ratio) ? $this->font_ratio : 0.4;

        if ((float)$ratio < 0.1 || (float)$ratio >= 1) {
            $ratio = 0.4;
        }

        if (!is_readable($this->ttf_file)) {
            // this will not catch missing fonts after the first!
            $this->perturbation = 0;
            imagestring($this->im, 4, 10, ($this->image_height / 2) - 5, 'Failed to load TTF font file!', $this->gdtextcolor);

            return ;
        }

        if ($this->perturbation > 0) {
            $width     = $this->image_width * $this->iscale;
            $height    = $this->image_height * $this->iscale;
            $font_size = $height * $ratio;
            $im        = &$this->tmpimg;
            $scale     = $this->iscale;
        } else {
            $height    = $this->image_height;
            $width     = $this->image_width;
            $font_size = $this->image_height * $ratio;
            $im        = &$this->im;
            $scale     = 1;
        }

        if ($this->use_random_spaces && strpos($captcha_text, ' ') === false) {
            if (mt_rand(1, 100) % 5 > 0) { // ~20% chance no spacing added
                $index  = mt_rand(1, strlen($captcha_text) -1);
                $spaces = mt_rand(1, 3);

                // in general, we want all characters drawn close together to
                // prevent easy segmentation by solvers, but this adds random
                // spacing between two groups to make character positioning
                // less normalized.

                $captcha_text = sprintf(
                    '%s%s%s',
                    substr($captcha_text, 0, $index),
                    str_repeat(' ', $spaces),
                    substr($captcha_text, $index)
                );
            }
        }

        $fonts    = array();  // list of fonts corresponding to each char $i
        $angles   = array();  // angles corresponding to each char $i
        $distance = array();  // distance from current char $i to previous char
        $dims     = array();  // dimensions of each individual char $i
        $txtWid   = 0;        // width of the entire text string, including spaces and distances

        // Character positioning and angle

        $angle0 = mt_rand(10, 20);
        $angleN = mt_rand(-20, 10);

        if ($this->use_text_angles == false) {
            $angle0 = $angleN = $step = 0;
        }

        if (mt_rand(0, 99) % 2 == 0) {
            $angle0 = -$angle0;
        }
        if (mt_rand(0, 99) % 2 == 1) {
            $angleN = -$angleN;
        }

        $step   = abs($angle0 - $angleN) / (max(1, strlen($captcha_text) - 1));
        $step   = ($angle0 > $angleN) ? -$step : $step;
        $angle  = $angle0;

        for ($c = 0; $c < strlen($captcha_text); ++$c) {
            $font     = $this->ttf_file; // select random font from list for this character
            $fonts[]  = $font;
            $angles[] = $angle;  // the angle of this character
            $dist     = mt_rand(-2, 0) * $scale; // random distance between this and next character
            $distance[] = $dist;
            $char     = substr($captcha_text, $c, 1); // the character to draw for this sequence

            $dim = $this->getCharacterDimensions($char, $font_size, $angle, $font); // calculate dimensions of this character

            $dim[0] += $dist;   // add the distance to the dimension (negative to bring them closer)
            $txtWid += $dim[0]; // increment width based on character width

            $dims[] = $dim;

            $angle += $step; // next angle

            if ($angle > 20) {
                $angle = 20;
                $step  = $step * -1;
            } elseif ($angle < -20) {
                $angle = -20;
                $step  = -1 * $step;
            }
        }

        $nextYPos = function($y, $i, $step) use ($height, $scale, $dims) {
            static $dir = 1;

            if ($y + $step + $dims[$i][2] + (10 * $scale) > $height) {
                $dir = 0;
            } elseif ($y - $step - $dims[$i][2] < $dims[$i][1] + $dims[$i][2] + (5 * $scale)) {
                $dir = 1;
            }

            if ($dir) {
                $y += $step;
            } else {
                $y -= $step;
            }

            return $y;
        };

        $cx = floor($width / 2 - ($txtWid / 2));
        $x  = mt_rand(5 * $scale, max($cx * 2 - (5 * $scale), 5 * $scale));

        if ($this->use_random_baseline) {
            $y = mt_rand($dims[0][1], $height - 10);
        } else {
            $y = ($height / 2 + $dims[0][1] / 2 - $dims[0][2]);
        }

        $st = $scale * mt_rand(5, 10);

        for ($c = 0; $c < strlen($captcha_text); ++$c) {
            $font  = $fonts[$c];
            $char  = substr($captcha_text, $c, 1);
            $angle = $angles[$c];
            $dim   = $dims[$c];

            if ($this->use_random_baseline) {
                $y = $nextYPos($y, $c, $st);
            }

            imagettftext(
                $im,
                $font_size,
                $angle,
                (int)$x,
                (int)$y,
                $this->gdtextcolor,
                $font,
                $char
            );

            if ($this->use_random_boxes && strlen(trim($char)) && mt_rand(1,100) % 5 == 0) {
                imagesetthickness($im, 3);
                imagerectangle($im, $x, $y - $dim[1] + $dim[2], $x + $dim[0], $y + $dim[2], $this->gdtextcolor);
            }

            if ($c == ' ') {
                $x += $dim[0];
            } else {
                $x += $dim[0] + $distance[$c];
            }
        }
    }

    /**
     * Get the width and height (in points) of a character for a given font,
     * angle, and size.
     *
     * @param string $char The character to get dimensions for
     * @param number $size The font size, in points
     * @param number $angle The angle of the text
     * @return number[] A 3-element array representing the width, height and baseline of the text
     */
    protected function getCharacterDimensions($char, $size, $angle, $font)
    {
        $box = imagettfbbox($size, $angle, $font, $char);

        return array($box[2] - $box[0], max($box[1] - $box[7], $box[5] - $box[3]), $box[1]);
    }

    /**
     * Copies the captcha image to the final image with distortion applied
     */
    protected function distortedCopy()
    {
        $numpoles = 3;       // distortion factor
        $px       = array(); // x coordinates of poles
        $py       = array(); // y coordinates of poles
        $rad      = array(); // radius of distortion from pole
        $amp      = array(); // amplitude
        $x        = ($this->image_width / 4); // lowest x coordinate of a pole
        $maxX     = $this->image_width - $x;  // maximum x coordinate of a pole
        $dx       = mt_rand($x / 10, $x);     // horizontal distance between poles
        $y        = mt_rand(20, $this->image_height - 20);  // random y coord
        $dy       = mt_rand(20, $this->image_height * 0.7); // y distance
        $minY     = 20;                                     // minimum y coordinate
        $maxY     = $this->image_height - 20;               // maximum y cooddinate

        // make array of poles AKA attractor points
        for ($i = 0; $i < $numpoles; ++ $i) {
            $px[$i]  = ($x + ($dx * $i)) % $maxX;
            $py[$i]  = ($y + ($dy * $i)) % $maxY + $minY;
            $rad[$i] = mt_rand($this->image_height * 0.4, $this->image_height * 0.8);
            $tmp     = ((- $this->frand()) * 0.15) - .15;
            $amp[$i] = $this->perturbation * $tmp;
        }

        $bgCol   = imagecolorat($this->tmpimg, 0, 0);
        $width2  = $this->iscale * $this->image_width;
        $height2 = $this->iscale * $this->image_height;
        imagepalettecopy($this->im, $this->tmpimg); // copy palette to final image so text colors come across

        // loop over $img pixels, take pixels from $tmpimg with distortion field
        for ($ix = 0; $ix < $this->image_width; ++ $ix) {
            for ($iy = 0; $iy < $this->image_height; ++ $iy) {
                $x = $ix;
                $y = $iy;
                for ($i = 0; $i < $numpoles; ++ $i) {
                    $dx = $ix - $px[$i];
                    $dy = $iy - $py[$i];
                    if ($dx == 0 && $dy == 0) {
                        continue;
                    }
                    $r = sqrt($dx * $dx + $dy * $dy);
                    if ($r > $rad[$i]) {
                        continue;
                    }
                    $rscale = $amp[$i] * sin(3.14 * $r / $rad[$i]);
                    $x += $dx * $rscale;
                    $y += $dy * $rscale;
                }
                $c = $bgCol;
                $x *= $this->iscale;
                $y *= $this->iscale;
                if ($x >= 0 && $x < $width2 && $y >= 0 && $y < $height2) {
                    $c = imagecolorat($this->tmpimg, $x, $y);
                }
                if ($c != $bgCol) { // only copy pixels of letters to preserve any background image
                    imagesetpixel($this->im, $ix, $iy, $c);
                }
            }
        }
    }

    /**
     * Draws distorted lines on the image
     */
    protected function drawLines()
    {
        for ($line = 0; $line < $this->num_lines; ++ $line) {
            $x = $this->image_width * (1 + $line) / ($this->num_lines + 1);
            $x += (0.5 - $this->frand()) * $this->image_width / $this->num_lines;
            $y = mt_rand($this->image_height * 0.1, $this->image_height * 0.9);

            $theta = ($this->frand() - 0.5) * M_PI * 0.33;
            $w = $this->image_width;
            $len = mt_rand($w * 0.4, $w * 0.7);
            $lwid = mt_rand(0, 2);

            $k = $this->frand() * 0.6 + 0.2;
            $k = $k * $k * 0.5;
            $phi = $this->frand() * 6.28;
            $step = 0.5;
            $dx = $step * cos($theta);
            $dy = $step * sin($theta);
            $n = $len / $step;
            $amp = 1.5 * $this->frand() / ($k + 5.0 / $len);
            $x0 = $x - 0.5 * $len * cos($theta);
            $y0 = $y - 0.5 * $len * sin($theta);

            for ($i = 0; $i < $n; ++ $i) {
                $x = $x0 + $i * $dx + $amp * $dy * sin($k * $i * $step + $phi);
                $y = $y0 + $i * $dy - $amp * $dx * sin($k * $i * $step + $phi);
                imagefilledrectangle($this->im, $x, $y, $x + $lwid, $y + $lwid, $this->gdlinecolor);
            }
        }
    }

    /**
     * Draws random noise on the image
     */
    protected function drawNoise()
    {
        if ($this->noise_level > 10) {
            $noise_level = 10;
        } else {
            $noise_level = $this->noise_level;
        }

        $noise_level *= M_LOG2E;

        for ($x = 1; $x < $this->image_width; $x += 20) {
            for ($y = 1; $y < $this->image_height; $y += 20) {
                for ($i = 0; $i < $noise_level; ++$i) {
                    $x1 = mt_rand($x, $x + 20);
                    $y1 = mt_rand($y, $y + 20);
                    $size = mt_rand(1, 3);

                    if ($x1 - $size <= 0 && $y1 - $size <= 0) continue; // dont cover 0,0 since it is used by imagedistortedcopy
                    imagefilledarc($this->im, $x1, $y1, $size, $size, 0, mt_rand(180,360), $this->gdlinecolor, IMG_ARC_PIE);
                }
            }
        }
    }

    /**
     * Print signature text on image
     */
    protected function addSignature()
    {
        $bbox = imagettfbbox(10, 0, $this->signature_font, $this->image_signature);
        $textlen = $bbox[2] - $bbox[0];
        $x = $this->image_width - $textlen - 5;
        $y = $this->image_height - 3;

        imagettftext($this->im, 10, 0, $x, $y, $this->gdsignaturecolor, $this->signature_font, $this->image_signature);
    }

    /**
     * Sends the appropriate image and cache headers and outputs image to the browser
     */
    protected function output()
    {
        if ($this->canSendHeaders()) {
            header("Content-Type: image/png");
            imagepng($this->im);
        } else {
            echo '<hr /><strong>Something went wrong.</strong>';
        }

        imagedestroy($this->im);

        exit;
    }

    /**
     * Generates a random captcha code from the set character set
     *
     * @see Securimage::$charset  Charset option
     * @return string A randomly generated CAPTCHA code
     */
    protected function generateCode()
    {
        $code = '';

        for($i = 1, $cslen = strlen($this->charset); $i <= $this->code_length; ++$i) {
            $code .= substr($this->charset, mt_rand(0, $cslen - 1), 1);
        }

        return $code;
    }

    /**
     * Checks to see if headers can be sent and if any error has been output
     * to the browser
     *
     * @return bool true if it is safe to send headers, false if not
     */
    protected function canSendHeaders()
    {
        if (headers_sent()) {
            // output has been flushed and headers have already been sent
            return false;
        } else if (strlen((string)ob_get_contents()) > 0) {
            // headers haven't been sent, but there is data in the buffer that will break image and audio data
            return false;
        }

        return true;
    }

    /**
     * Return a random float between 0 and 0.9999
     *
     * @return float Random float between 0 and 0.9999
     */
    protected function frand()
    {
        return 0.0001 * mt_rand(0,9999);
    }

    protected function siteURL()
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $domainName = $_SERVER['HTTP_HOST'].'/';
        return $protocol.$domainName;
    }

    /**
     * Convert an html color code to a Securimage_Color
     * @param string $color
     * @param Colorize|string $default The defalt color to use if $color is invalid
     * @return Colorize
     * @throws \Exception
     */
    protected function initColor($color, $default)
    {
        if ($color == null) {
            return new Colorize($default);
        } else if (is_string($color)) {
            try {
                return new Colorize($color);
            } catch(\Exception $e) {
                return new Colorize($default);
            }
        } else if (is_array($color) && sizeof($color) == 3) {
            return new Colorize($color[0], $color[1], $color[2]);
        } else {
            return new Colorize($default);
        }
    }
}