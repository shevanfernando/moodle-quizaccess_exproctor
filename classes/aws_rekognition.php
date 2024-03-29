<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * quizaccess_exproctor file description here.
 *
 * @package    quizaccess_exproctor
 * @copyright  2022 Shevan Fernando <w.k.b.s.t.fernando@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace quizaccess_exproctor;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/mod/quiz/accessrule/exproctor/aws_sdk/aws-autoloader.php');

use Aws\Rekognition\Exception\RekognitionException;
use Aws\Rekognition\RekognitionClient;
use Aws\Result;

class aws_rekognition {
    private $rekognitionclient;
    private $s3client;

    public function __construct() {
        $this->s3client = new aws_s3();

        $data = $this->s3client->get_data();

        $this->rekognitionclient = new RekognitionClient([
            'version' => 'latest',
            'region' => $data['awsregion'],
            'credentials' => [
                'key' => $data['awsaccesskey'],
                'secret' => $data['awssecretkey']
            ]
        ]);
    }

    /**
     * @param $bucketname
     * @param $imagedata
     * @param $filename
     *
     * @return array|Result|string
     */
    public function store_evidence_which_false_ai_proctor(
        $bucketname,
        $imagedata,
        $filename
    ) {
        try {
            $isthereanyphone = $this->get_object_details($imagedata);
            $isfacialanalysisfalse = $this->get_face_details($imagedata);

            if ($isfacialanalysisfalse || $isthereanyphone) {
                return $this->s3client->save_image($bucketname, $imagedata,
                    $filename);
            }

            return array();
        } catch (RekognitionException $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    /**
     * Get objects
     *
     * @param $image
     *
     * @return bool
     */
    public function get_object_details($image): bool {
        try {
            // Call DetectLabels.
            $result = $this->rekognitionclient->DetectLabels(array(
                "Features" => array('GENERAL_LABELS'),
                'Image' => array(
                    'Bytes' => $image,
                ),
            ));

            $dataset = $result->get("Labels");

            foreach ($dataset as $data) {
                if ($data["Name"] === "Mobile Phone" || $data["Name"] === "Phone") {
                    return true;
                }
            }

            return false;
        } catch (RekognitionException $e) {
            return "Error: " . $e->getMessage();
        }
    }

    /**
     * Get facial analysis
     *
     * @param $image
     *
     * @return bool
     */
    public function get_face_details($image): bool {
        try {
            // Call DetectFaces.
            $result = $this->rekognitionclient->DetectFaces(array(
                    'Image' => array(
                        'Bytes' => $image,
                    ),
                    'Attributes' => array('ALL')
                )
            );

            $dataset = $result->get("FaceDetails");

            if (count($dataset) !== 1) {
                return true;
            } else {
                if ($dataset[0]["EyesOpen"]["Confidence"] < 90 || $dataset[0]["MouthOpen"]["Value"] ||
                    $dataset[0]["EyesOpen"]["Value"] === false) {
                    return true;
                }
            }

            return false;
        } catch (RekognitionException $e) {
            return "Error: " . $e->getMessage();
        }
    }
}
