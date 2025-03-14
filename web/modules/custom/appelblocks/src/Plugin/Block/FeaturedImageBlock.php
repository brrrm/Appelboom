<?php

namespace Drupal\appelblocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\file\Entity\File;

/**
 * Provides a 'featured image' block.
 *
 * @Block(
 *   id = "appelboom_fetured_image",
 *   admin_label = @Translation("Featured image for Appelboom"),
 *   category = @Translation("Appelboom")
 * )
 */

class FeaturedImageBlock extends BlockBase{
	
	public function build(){
		$cache_tags = array();

		// get current node
		$node = \Drupal::routeMatch()->getParameter('node');
		if($node){
			$cache_tags = $node->getCacheTags();
		}

		$title = NULL;
		
		if($node && $node->hasField('field_featured_image') && !$node->get('field_featured_image')->isEmpty()){
			$media = $node->get('field_featured_image')->entity;
			$image = \Drupal::entityTypeManager()->getViewBuilder('media')->view($media, 'header');
			$request = \Drupal::request();
			$route_match = \Drupal::routeMatch();
			$title = \Drupal::service('title_resolver')->getTitle($request, $route_match->getRouteObject());
		}

		if($node && $node->hasField('field_services') && !$node->get('field_services')->isEmpty()){
			$display_options = [
				'label' => 'above',
				'type' => 'entity_reference_entity_view',
				'settings' => [
					'view_mode' => 'icon_link',
				],
			];
			$services = $node->get('field_services')->view($display_options);
		}

		if($node && $node->hasField('field_lead_consultant') && !$node->get('field_lead_consultant')->isEmpty()){
			$display_options = [
				'label' => 'above',
				'type' => 'entity_reference_entity_view',
				'settings' => [
					'view_mode' => 'icon_link',
				],
			];
			$consultant = $node->get('field_lead_consultant')->view($display_options);
		}

		if($node && $node->hasField('field_case_study__client_name') && !$node->get('field_case_study__client_name')->isEmpty()){
			$display_options = [
				'label' => 'above',
				'type' => 'string',
			];
			$client = $node->get('field_case_study__client_name')->view($display_options);
		}
			
		if(!isset($node)){
			return false;
		}

		return [
			'main'			=> [
				'#prefix'		=> '<div class="main">',
				'#suffix'		=> '</div>',
				'title'	=> [
					'#markup'	=> sprintf('<h1 id="header-page-title" class="page__title title">%s</h1>', $title)
				],
				'image'	=> $image,
			],
			'sidebar'		=> [
				'#prefix'		=> '<div class="sidebar">',
				'client'		=> $client ?? NULL,
				'services'		=> $services ?? NULL,
				'consultant'	=> $consultant ?? NULL,
				'#suffix'		=> '</div>',
			]
		];

	}
}
