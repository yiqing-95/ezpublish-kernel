<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\HandlerTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Tests\FieldType;
use eZ\Publish\Core\Persistence\Legacy,
    eZ\Publish\Core\FieldType,
    eZ\Publish\SPI\Persistence\Content,
    eZ\Publish\SPI\Persistence\Content\Field,
    eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints;

/**
 * Integration test for legacy storage field types
 *
 * This abstract base test case is supposed to be the base for field type
 * integration tests. It basically calls all involved methods in the field type 
 * ``Converter`` and ``Storage`` implementations. Fo get it working implement
 * the abstract methods in a sensible way.
 *
 * The following actions are performed by this test using the custom field
 * type:
 *
 * - Create a new content type with the given field type
 * - Load create content type
 * - Create content object of new content type
 * - Load created content
 * - Copy created content
 * - Remove copied content
 *
 * @group integration
 */
class ImageIntergrationTest extends BaseIntegrationTest
{
    /**
     * If the temporary directory should be removed after the tests.
     *
     * @var bool
     */
    protected static $removeTmpDir = true;

    /**
     * Temporary directory
     *
     * @var string
     */
    protected static $tmpDir;

    public static function setUpBeforeClass()
    {
        $tmpFile = tempnam( sys_get_temp_dir(), 'eZ_FieldType_ImageIntegrationTest' );

        // Convert file into directory
        unlink( $tmpFile );
        mkdir( $tmpFile );

        self::$tmpDir = $tmpFile;
    }

    public static function tearDownAfterClass()
    {
        self::removeRecursive( self::$tmpDir );
    }

    /**
     * Removes the given directory path recursively
     *
     * @param string $dir
     * @return void
     */
    protected static function removeRecursive( $dir )
    {
        if ( !self::$removeTmpDir )
        {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $dir,
                \FileSystemIterator::KEY_AS_PATHNAME | \FileSystemIterator::SKIP_DOTS | \ FilesystemIterator::CURRENT_AS_FILEINFO

            ),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ( $iterator as $path => $fileInfo )
        {
            if ( $fileInfo->isDir() )
            {;
                rmdir( $path );
            }
            else
            {
                unlink( $path );
            }
        }

        rmdir( $dir );
    }

    /**
     * Get name of tested field tyoe
     *
     * @return string
     */
    public function getTypeName()
    {
        return 'ezimage';
    }

    /**
     * Get handler with required custom field types registered
     *
     * @return Handler
     */
    public function getCustomHandler()
    {
        $handler = $this->getHandler();

        $handler->getStorageRegistry()->register(
            'ezimage',
            new FieldType\Image\ImageStorage(
                array(
                    'LegacyStorage' => new FieldType\Image\ImageStorage\Gateway\LegacyStorage(),
                ),
                new FieldType\Image\FileService\LocalFileService(
                    self::$tmpDir,
                    'var/my_site/storage'
                )
            )
        );
        $handler->getFieldValueConverterRegistry()->register(
            'ezimage',
            new Legacy\Content\FieldValue\Converter\Image()
        );

        return $handler;
    }

    /**
     * Returns the FieldTypeConstraints to be used to create a field definition
     * of the FieldType under test.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints
     */
    public function getTypeConstraints()
    {
        return new Content\FieldTypeConstraints(
            array(
                'validators' => array(
                    'FileSizeValidator' => array(
                        'maxFileSize' => 2 * 1024 * 1024, // 2 MB
                    )
                )
            )
        );
    }

    /**
     * Get field definition data values
     *
     * This is a PHPUnit data provider
     *
     * @return array
     */
    public function getFieldDefinitionData()
    {
        return array(
            // The ezint field type does not have any special field definition
            // properties
            array( 'fieldType', 'ezimage' ),
            array(
                'fieldTypeConstraints',
                new Content\FieldTypeConstraints(
                    array(
                        'validators' => array(
                            'FileSizeValidator' => array(
                                'maxFileSize' => 2 * 1024 * 1024, // 2 MB
                            )
                        )
                    )
                )
            ),
        );
    }

    /**
     * Get initial field value
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    public function getInitialValue()
    {
        return new Content\FieldValue( array(
            'data'         => null,
            'externalData' => array(
                'path' => __DIR__ . '/_fixtures/image.jpg',
                'fileName' => 'Ice-Flower.jpg',
                'alternativeText' => 'An icy flower.',
            ),
            'sortKey'      => '',
        ) );
    }

    /**
     * Asserts that the loaded field data is correct
     *
     * Performs assertions on the loaded field, mainly checking that the
     * $field->value->externalData is loaded correctly. If the loading of
     * external data manipulates other aspects of $field, their correctness
     * also needs to be asserted. Make sure you implement this method agnostic
     * to the used SPI\Persistence implementation!
     */
    public function assertLoadedFieldDataCorrect( Field $field )
    {
        $this->assertNotNull( $field->value->data );

        $this->assertTrue(
            file_exists( self::$tmpDir . '/' . $field->value->data['path'] )
        );

        $this->assertEquals( 'Ice-Flower.jpg', $field->value->data['fileName'] );

        $this->assertEquals( 'An icy flower.', $field->value->data['alternativeText'] );

        $this->assertNull( $field->value->externalData );
    }

    /**
     * Get update field value.
     *
     * Use to update the field
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    public function getUpdatedValue()
    {
        return new Content\FieldValue( array(
            'data'         => null,
            'externalData' => array(
                'path' => __DIR__ . '/_fixtures/image.png',
                'fileName' => 'Blueish-Blue.jpg',
                'alternativeText' => 'This blue is so blueish.',
            ),
            'sortKey'      => '',
        ) );
    }

    /**
     * Asserts that the updated field data is loaded correct
     *
     * Performs assertions on the loaded field after it has been updated,
     * mainly checking that the $field->value->externalData is loaded
     * correctly. If the loading of external data manipulates other aspects of
     * $field, their correctness also needs to be asserted. Make sure you
     * implement this method agnostic to the used SPI\Persistence
     * implementation!
     *
     * @return void
     */
    public function assertUpdatedFieldDataCorrect( Field $field )
    {
        $this->assertNotNull( $field->value->data );

        $this->assertTrue(
            file_exists( self::$tmpDir . '/' . $field->value->data['path'] )
        );

        $this->assertEquals( 'Blueish-Blue.jpg', $field->value->data['fileName'] );

        $this->assertEquals( 'This blue is so blueish.', $field->value->data['alternativeText'] );

        $this->assertNull( $field->value->externalData );
    }
}
