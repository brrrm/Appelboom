<?php

namespace Drupal\appelblocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\file\Entity\File;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Provides a 'featured image' block.
 *
 * @Block(
 *   id = "appelboom_person_header",
 *   admin_label = @Translation("headerblock for persons for Appelboom"),
 *   category = @Translation("Appelboom")
 * )
 */

class PersonHeaderBlock extends BlockBase{
	
	public function build(){
		$cache_tags = array();

		// get current node
		$node = \Drupal::routeMatch()->getParameter('node');
		if(!$node){
			return false;
		}

		$cache_tags = $node->getCacheTags();
		$title = NULL;
		
		if($node->hasField('field_featured_image') && !$node->get('field_featured_image')->isEmpty()){
			$image = $node->get('field_featured_image')->view([
				'label'		=> 'hidden',
				'type'		=> 'media_thumbnail',
				'settings'	=> [
					'image_style'	=> '4_1_1200x300_focal_point_webp'
				]
			]);

			$request = \Drupal::request();
			$route_match = \Drupal::routeMatch();
			$title = \Drupal::service('title_resolver')->getTitle($request, $route_match->getRouteObject());
			$title = [
				'#prefix'	=> '<div class="title-subtitle">',
				'#markup'	=> sprintf('<h1 id="header-page-title" class="page__title title">%s</h1>', $title),
				'#suffix'	=> '</div>',
			];
		}
		if($node->hasField('field_person__role_job_title') && !$node->get('field_person__role_job_title')->isEmpty()){
			$subtitle = $node->get('field_person__role_job_title')->value;
			$title['#suffix'] = sprintf('<p class="subtitle"><em>%s</em></p></div>', $subtitle);
		}

		if($node->hasField('field_description') && !$node->get('field_description')->isEmpty()){

			$display_options = [
				'label' => 'hidden',
				'type' => 'string',
			];
			$intro = $node->get('field_description')->view($display_options);
		}

		$email = $node->get('field_person__email')->isEmpty()? NULL : $node->get('field_person__email')->value;
		$phone = $node->get('field_person__phone_number')->isEmpty()? NULL : $node->get('field_person__phone_number')->value;
		$linkedinUrl = $node->get('field_linkedin')->isEmpty()? NULL : $node->get('field_linkedin')->first()->getUrl();
		$linkedinUrl->setOptions([
			'attributes' => [
				'class' 	=> ['linkedIn-link'], 
				'target' 	=> '_blank',
				'rel'		=> 'nofollow'
			] 
		]);

		$portrait = $node->get('field_portrait')->view([
			'label'		=> 'hidden',
			'type'		=> 'media_thumbnail',
			'settings'	=> [
				'image_style'	=> '1_1_500x500_focal_point_webp'
			]
		]);

		$return =  [
			'header'		=> $image ?? NULL,
			'left'			=> [
				'#prefix'		=> '<div class="left">',
				'portrait'		=> $portrait ?? NULL,
				'title'			=> $title,
				'#suffix'		=> '</div>'
			],
			'right'			=> [
				'#prefix'		=> '<div class="right">',
				'#suffix'		=> '</div>',
				'links'			=> [
					'#theme' 	=> 'item_list',
					'#items'	=> [
						Link::fromTextAndUrl($email, Url::fromUri('mailto:' . $email, ['attributes' => ['class' => ['email-link']]] ))->toRenderable(),
						Link::fromTextAndUrl('linkedin', $linkedinUrl)->toRenderable(),
						Link::fromTextAndUrl($phone, Url::fromUri('tel:'.$phone, ['attributes' => ['class' => ['phone-link']]] ))->toRenderable(),
					]
				]
			],
			'intro'			=> $intro ?? NULL
		];

		return $return;

	}
}
