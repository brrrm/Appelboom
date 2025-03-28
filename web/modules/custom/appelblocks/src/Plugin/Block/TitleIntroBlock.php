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
		$node = \Drupal::routeMatch()->getParameter('node');

		if(!$node){
			return false;
		}

		$cache_tags = $node->getCacheTags();
		
		$request = \Drupal::request();
		$route_match = \Drupal::routeMatch();
		$title = \Drupal::service('title_resolver')->getTitle($request, $route_match->getRouteObject());
		$title = [
			'#prefix'	=> '<div class="title-subtitle">',
			'#markup'	=> sprintf('<h1 id="header-page-title" class="page__title title">%s</h1>', $title),
			'#suffix'	=> '</div>'
		];

		if($node->hasField('field_subtitle') && !$node->get('field_subtitle')->isEmpty()){
			$display_options = [
				'label' => 'hidden',
				'type' => 'string',
			];
			$subtitle = $node->get('field_subtitle')->value;
			$title['#suffix'] = sprintf('<p class="subtitle"><em>%s</em></p></div>', $subtitle);
		}
		
		if($node->hasField('field_featured_image') && !$node->get('field_featured_image')->isEmpty()){
			$image = $node->get('field_featured_image')->view([
				'label'		=> 'hidden',
				'type'		=> 'media_thumbnail',
				'settings'	=> [
					'image_style'	=> '4_3_1800x1350_focal_point_webp'
				]
			]);
		}

		if($node->hasField('field_description') && !$node->get('field_description')->isEmpty()){

			$display_options = [
				'label' => 'hidden',
				'type' => 'string',
			];
			$intro = $node->get('field_description')->view($display_options);
		}

		$return =  [
			$image ?? NULL,
			$title,
			$intro,
			'#attributes'	=> [
				'class'		=> ['clearfix']
			],
			'#cache' => [
				'tags' => $cache_tags,
				'contexts' => ['url.path']
			]
		];

		return $return;

	}
}
