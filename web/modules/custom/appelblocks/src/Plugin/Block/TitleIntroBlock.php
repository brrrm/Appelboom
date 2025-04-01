<?php

namespace Drupal\appelblocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManager;

/**
 * Provides a 'title' block.
 *
 * @Block(
 *   id = "appelboom_title_intro",
 *   admin_label = @Translation("Title, subtitle and intro for Appelboom"),
 *   category = @Translation("Appelboom")
 * )
 */

class TitleIntroBlock extends BlockBase implements ContainerFactoryPluginInterface{

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
		$cache_tags = array();

		// get current node
		$entity = \Drupal::routeMatch()->getParameter('node') ?? \Drupal::routeMatch()->getParameter('taxonomy_term');

		if(!$entity){
			return false;
		}

		$cache_tags = $entity->getCacheTags();
		
		$title = $entity->label();
		$title = [
			'#prefix'	=> '<div class="title-subtitle">',
			'#markup'	=> sprintf('<h1 id="header-page-title" class="page__title title">%s</h1>', $title),
			'#suffix'	=> '</div>'
		];

		if($entity->hasField('field_subtitle') && !$entity->get('field_subtitle')->isEmpty()){
			$display_options = [
				'label' => 'hidden',
				'type' => 'string',
			];
			$subtitle = $entity->get('field_subtitle')->value;
			$title['#suffix'] = sprintf('<p class="subtitle"><em>%s</em></p></div>', $subtitle);
		}
		
		if($entity->hasField('field_featured_image') && !$entity->get('field_featured_image')->isEmpty()){
			$image = $entity->get('field_featured_image')->view([
				'label'		=> 'hidden',
				'type'		=> 'media_thumbnail',
				'settings'	=> [
					'image_style'	=> '4_3_1800x1350_focal_point_webp'
				]
			]);
		}
		
		if($entity->hasField('field_icon') && !$entity->get('field_icon')->isEmpty()){
			$image = $entity->get('field_icon')->view([
				'label'		=> 'hidden',
				'type'		=> 'media_thumbnail',
				'settings'	=> [
					'image_style'	=> '4_3_1800x1350_focal_point_webp'
				]
			]);
		}

		if($entity->hasField('field_description') && !$entity->get('field_description')->isEmpty()){
			$display_options = [
				'label' => 'hidden',
				'type' => 'string',
			];
			$intro = $entity->get('field_description')->view($display_options);
		}

		$return =  [
			'image'	=> $image ?? NULL,
			'title'	=> $title,
			'intro'	=> $intro,
			'#attributes'	=> [
				'class'		=> ['clearfix', 'titlesubtitleandintroforappelboom']
			],
			'#cache' => [
				'tags' => $cache_tags,
				'contexts' => ['url.path']
			]
		];

		return $return;

	}
}
