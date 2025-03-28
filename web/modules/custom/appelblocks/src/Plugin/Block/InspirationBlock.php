<?php

namespace Drupal\appelblocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\file\Entity\File;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManager;

/**
 * Provides a 'featured image' block.
 *
 * @Block(
 *   id = "appelboom_inspiration_block",
 *   admin_label = @Translation("Inspiration block for Appelboom"),
 *   category = @Translation("Appelboom")
 * )
 */

class InspirationBlock extends BlockBase implements ContainerFactoryPluginInterface {

	/**
	 * The entity type manager.
	 *
	 * @var \Drupal\Core\Entity\EntityTypeManagerInterface
	 */
	protected $entityTypeManager;


	/**
	* Constructs a Drupalist object.
	*
	* @param array $configuration
	*   A configuration array containing information about the plugin instance.
	* @param string $plugin_id
	*   The plugin_id for the plugin instance.
	* @param mixed $plugin_definition
	*   The plugin implementation definition.
	* @param \Drupal\Core\Session\AccountInterface $currentUser
	*   The current_user.
	*/
	public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManager $entity_type_manager) {
		parent::__construct($configuration, $plugin_id, $plugin_definition);
		$this->entityTypeManager = $entity_type_manager;
	}

	/**
	* {@inheritdoc}
	*/
	public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
		return new static(
		  $configuration,
		  $plugin_id,
		  $plugin_definition,
		  $container->get('entity_type.manager')
		);
	}
	
	public function build(){
		$config = $this->getConfiguration();
		$node_id = $config['node_id'] ?? NULL;
		if($node_id && $node = $this->entityTypeManager->getStorage('node')->load($node_id)){
			$teaser = $this->entityTypeManager->getViewBuilder('node')->view($node, 'card');
		}

		/*$text = $config['inspiration_text']['value'] ?? 'SCHRIJF EEN TEKST';
		if (!empty($config['image'][0]) && $file = File::load($config['image'][0])) {
			$image = [
				'#theme' 		=> 'image_style',
				'#style_name' 	=> 'medium',
				'#uri' 			=> $file->getFileUri(),
			];
		}*/

		return [
			'teaser' => $teaser ?? NULL,
		];
	}

	/**
	* {@inheritdoc}
	*/  
	public function defaultConfiguration() {
		return [
			'image' 			=> $this->t(''),
			'inspiration_text'	=> 'ja, hier is de default text'
		];
	}

	/**
	* {@inheritdoc}
	*/
	public function blockForm($form, FormStateInterface $form_state) {
		$form = parent::blockForm($form, $form_state);

		$form['node_id'] = [
			'#type' => 'entity_autocomplete',
			'#title' => 'Medewerker',
			'#target_type' => 'node',
			'#selection_settings' => [
				'target_bundles' => ['person'],
			],
			'#default_value' => !empty($this->configuration['node_id']) ? $this->entityTypeManager->getStorage('node')->load($this->configuration['node_id']) : NULL,
			'#selection_handler' => 'default',
			'#description' => $this->t('Select a media item to display in this block.'),
		];

		/*
		$form['inspiration_text'] = [
			'#type' 			=> 'text_format',
			'#title' 			=> $this->t('Text'),
			'#description' 		=> $this->t('Who do you want to say hello to?'),
			'#default_value' 	=> $this->configuration['inspiration_text']['value'] ?? '',
			'#format' 			=> $this->configuration['inspiration_text']['format'] ?? 'content_format',
		];
    
		$form['image'] = array(
			'#type' 				=> 'managed_file',
			'#upload_location' 		=> 'public://block-images',
			'#title' 				=> t('Image'),
			'#upload_validators' 	=> [
				'FileExtension' => [
					'extensions' => 'png gif jpg',
				],
			],
			'#default_value' 		=> $this->configuration['image'] ?? '',
			'#description' 			=> t('The image to display'),
			'#required' 			=> true
		);
		*/

		return $form;
	}

	/**
	* {@inheritdoc}
	*/
	public function blockSubmit($form, FormStateInterface $form_state) {
		$values = $form_state->getValues();
		if (!empty($values['image'][0]) && $values['image'] != $this->configuration['image']) {
			$file = File::load($values['image'][0]);
			$file->setPermanent();
			$file->save();
		}

		$this->configuration['node_id'] = $values['node_id'];
		$this->configuration['inspiration_text'] = $values['inspiration_text'];
		$this->configuration['image'] = $values['image'];
	}

}