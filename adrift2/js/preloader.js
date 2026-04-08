THREE.Preloader = function ( webglContainer, options ) {

	var camera, scene, renderer, request, container;

	this.start = function () {
		container.style.display = '';
		options.wrapper.style.display = 'block';
		if(request)
			cancelAnimationFrame( request );
		//animate();
		render();
	};

	this.stop = function () {
		cancelAnimationFrame( request );
		container.style.display = 'none';
		options.wrapper.style.display = "none";
	};

	this.updateProgress = function( progress ){

		var bar = (window.innerWidth * 0.33),
			total = progress.totalModels + progress.totalTextures,
			loaded = progress.loadedModels + progress.loadedTextures;

		if ( total )
			bar = Math.floor( bar * loaded / total );

		if(options.message)
			options.message.innerHTML = 'Loading... Models ' + progress.loadedModels + '/' + progress.totalModels + ', textures ' + progress.loadedTextures + '/' + progress.totalTextures;
		options.bar.style.width = bar + "px";
	};

	init();

	function init () {

		createLoadScene();

		renderer = new THREE.WebGLRenderer( { antialias : true, alpha : true } );
		renderer.setPixelRatio( window.devicePixelRatio );
		renderer.setSize( window.innerWidth, window.innerHeight );
		//renderer.setClearColor( '#ffff00' );
		renderer.gammaInput = true;
		renderer.gammaOutput = true;
		document.body.insertBefore( renderer.domElement, options.wrapper );

		container = renderer.domElement;
		container.style.position = 'absolute';
		container.style.display = 'none';
		container.style.zIndex = 1006;
		container.style.top = container.style.left = 0;
		container.style.background = 'url(' + options.background +') no-repeat scroll center center';

		//

		window.addEventListener( 'resize', onWindowResize, false );

		setRelativeViewerContainer( );

	}

	function createLoadScene() {

		scene =  new THREE.Scene();
		camera = new THREE.PerspectiveCamera( 65, window.innerWidth / window.innerHeight, 1, 1000 );

		camera.position.z = 100;
		scene.add( camera );
/*
		var object, geometry, material, light, count = 500, range = 200;

		material = new THREE.MeshLambertMaterial( { color:0xffffff } );
		geometry = new THREE.BoxGeometry( 5, 5, 5 );

		for( var i = 0; i < count; i++ ) {

			object = new THREE.Mesh( geometry, material );

			object.position.x = ( Math.random() - 0.5 ) * range;
			object.position.y = ( Math.random() - 0.5 ) * range;
			object.position.z = ( Math.random() - 0.5 ) * range;

			object.rotation.x = Math.random() * 6;
			object.rotation.y = Math.random() * 6;
			object.rotation.z = Math.random() * 6;

			object.matrixAutoUpdate = false;
			object.updateMatrix();

			//scene.add( object );

		}
/*
		scene.matrixAutoUpdate = false;

		light = new THREE.PointLight( 0xffffff );
		scene.add( light );

		light = new THREE.DirectionalLight( 0x111111 );
		light.position.x = 1;
		scene.add( light );
*/
	}


	function onWindowResize () {

		setRelativeViewerContainer();


	}

	function setRelativeViewerContainer(){

		var width =  window.innerWidth;
		var height = window.innerHeight;

		camera.aspect = width/ height;
		camera.updateProjectionMatrix();

		renderer.setSize( width, height );

	}

	function animate () {
		request = requestAnimationFrame( animate );


		render();

	}

	function render(){
		renderer.clear();
		renderer.render( scene, camera );
	}


};