<?php namespace Atorscho\Crumbs;

use Illuminate\Http\Request;
use Illuminate\Routing\Router;

class Crumbs {

	/**
	 * The array of breadcrumb items.
	 *
	 * @var array
	 */
	protected $crumbs = [ ];

	/**
	 * @var Router
	 */
	protected $route;

	/**
	 * @var Request
	 */
	protected $request;

	/**
	 * @param Router  $route
	 * @param Request $request
	 */
	public function __construct( Router $route, Request $request )
	{
		$this->route   = $route;
		$this->request = $request;

		$this->autoAddItems();
	}

	/**
	 * Add new item to the breadcrumbs array.
	 *
	 * @param string $url
	 * @param string $title
	 * @param array  $parameters
	 */
	public function add( $url, $title, $parameters = [ ] )
	{
		$url = $this->evaluateLink($url, $parameters);

		$this->crumbs[] = new CrumbsItem($url, $title);
	}

	/**
	 * Add current page to the breadcrumbs array.
	 *
	 * @param string $title
	 */
	public function addCurrent( $title )
	{
		$this->add($this->route->current()->uri(), $title);
	}

	/**
	 * Add home page to the breadcrumbs array.
	 *
	 * @return void
	 */
	public function addHomePage()
	{
		$this->add(config('crumbs.homeUrl'), config('crumbs.homeTitle'));
	}

	/**
	 * Add admin page to the breadcrumbs array.
	 *
	 * @return void
	 */
	public function addAdminPage()
	{
		$this->add(config('crumbs.adminUrl'), config('crumbs.adminTitle'));
	}

	/**
	 * Render breadcrumbs HTML.
	 *
	 * @return string|bool
	 */
	public function render()
	{
		if ( !$this->hasItems() )
		{
			return false;
		}


		return view(config('crumbs.crumbsView'), [ 'crumbs' => $this->crumbs ])->render();
	}

	/**
	 * Get first item of the breadcrumbs array.
	 *
	 * @return mixed
	 */
	public function getFirstItem()
	{
		return $this->hasCrumbs() ? $this->crumbs[0] : false;
	}

	/**
	 * Get last item of the breadcrumbs array.
	 *
	 * @return mixed
	 */
	public function getLastItem()
	{
		return $this->hasCrumbs() ? end($this->crumbs) : false;
	}

	/**
	 * Get the breadcrumbs array.
	 *
	 * @return array
	 */
	public function getCrumbs()
	{
		return $this->crumbs;
	}

	/**
	 * Return true if breadcrumbs are not empty.
	 *
	 * @return bool
	 */
	protected function hasCrumbs()
	{
		return (bool) $this->crumbs && $this->hasManyItems();
	}

	/**
	 * Return true if breadcrumbs has at least one item.
	 *
	 * @return bool
	 */
	protected function hasItems()
	{
		return (bool) count($this->crumbs);
	}

	/**
	 * Return true if breadcrumbs have more than one item.
	 *
	 * @return bool
	 */
	protected function hasManyItems()
	{
		return count($this->crumbs) > 1;
	}

	/**
	 * Return a named route if it exists
	 *
	 * @param string $url
	 * @param array  $parameters
	 *
	 * @return string
	 */
	protected function evaluateLink( $url, $parameters )
	{
		// If provided $url is a route name...
		if ( $this->route->has($url) )
		{
			$url = route($url, $parameters);
		}
		// Or an absolute link.
		elseif ( !preg_match('/^(?:\w+:)?\/\//', $url) )
		{
			$url = url($url, $parameters);
		}

		return $url;
	}

	/**
	 * Automatically prefix breadcrumbs with default items.
	 *
	 * @return void
	 */
	protected function autoAddItems()
	{
		if ( config('crumbs.displayBothPages') )
		{
			$this->addHomePage();
			if ( $this->request->is(config('crumbs.adminPattern')) )
			{
				$this->addAdminPage();
			}
		}
		else
		{
			if ( config('crumbs.displayHomePage') && !$this->request->is(config('crumbs.adminPattern')) )
			{
				$this->addHomePage();
			}
			if ( config('crumbs.displayAdminPage') && $this->request->is(config('crumbs.adminPattern')) )
			{
				$this->addAdminPage();
			}
		}
	}

}
