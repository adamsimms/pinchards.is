/**
 * Created by Andrei Nadchuk on 16.06.16.
 * email: navikom11@mail.ru
 */
THREE.OrbitControls.prototype.focus = function ( object, callback, callbackEnd ) {

    var scope = this;

    var centerStart = scope.target.clone();
    var positionStart = scope.object.position.clone();
    var startFov = scope.object.fov;
    var startQuaternion = scope.object.quaternion;
    

    var arguments = {};

    if( object.position){
        arguments.position = { obj : positionStart, end : object.position };
    }

    if( object.target ){
        arguments.center = { obj : centerStart, end : object.target };
    }

    if( object.fov ){
        arguments.fov = { obj : startFov, end : object.fov };
    }

    if( object.quaternion ){
        arguments.quaternion = { obj : startQuaternion, end : object.quaternion };
    }


    this.enableRotate = false;
    this.enablePan = false;

    if( object.easing == null){

        callback( 'focus', arguments, undefined, function () {
            scope.enableRotate = true;
            scope.enablePan = true;

            callbackEnd();

            setArgs( object.quaternion,  object.position, object.fov, object.target );
        } );

    } else {
        callback( 'focus', arguments, animate, function () {
            scope.enableRotate = true;
            scope.enablePan = true;

            if(object.fov && scope.object.fov != object.fov){
                scope.object.fov = object.fov;
                scope.object.updateProjectionMatrix();
            }
            callbackEnd();
        } );
    }


    function animate ( args ) {

        var currentCenter = args.center ? args.center.obj : undefined;
        var currentPosition = args.position ? args.position.obj : undefined;
        var currentFov = args.fov ? args.fov.obj : undefined;
        var currentQuaternion = args.quaternion ? args.quaternion.obj : undefined;

        setArgs( currentQuaternion, currentPosition, currentFov, currentCenter );

    }

    function setArgs( currentQuaternion, currentPosition, currentFov, currentCenter ) {
        if(currentQuaternion){
            scope.object.quaternion = new THREE.Quaternion( currentQuaternion.x, currentQuaternion.y, currentQuaternion.z, currentQuaternion.w );
        }

        if( currentPosition )
            scope.object.position.copy( new THREE.Vector3( currentPosition.x, currentPosition.y, currentPosition.z ) );

        if( currentFov ) {
            scope.object.fov = currentFov;
            scope.object.updateProjectionMatrix();
        }


        if( currentCenter) {
            scope.target = new THREE.Vector3( currentCenter.x, currentCenter.y, currentCenter.z);
            scope.update();
        }
    }


};

/**
 *
 * @param object end params for controls animation
 * @param callback
 * @param callbackEnd
 */
THREE.OrbitControls.prototype.focus2 = function ( object, callback, callbackEnd ) {

    var scope = this;

    var arguments = {};

    if ( object.position ) {
        arguments.position = { obj : object.position, end : object.position };
    }

    if ( object.quaternion ) {
        arguments.quaternion = { obj : object.quaternion, end : object.quaternion };
    }


    callback( 'focus', arguments, animate, function () {

        callbackEnd();
    } );

    function animate ( args ) {

        var currentQuaternion = args.quaternion ? args.quaternion.start : undefined;
        var currentPosition = args.position ? args.position.start : undefined;

        if ( currentPosition )
            scope.object.position.copy( new THREE.Vector3( currentPosition.x, currentPosition.y, currentPosition.z ) );

        if ( currentQuaternion ){
            var quaternion = new THREE.Quaternion( (currentQuaternion.x *1), (currentQuaternion.y * 1), (currentQuaternion.z *1), (currentQuaternion.w *1) );
            var vector = scope.object.position.clone();
            var t_vector = object.t_position;
            scope.target.copy( t_vector );
            scope.update();
            console.log( "camera", scope.object.position, "t_vect", t_vector );
            //console.log( "q", quaternion, "t_vect", t_vect );
        }


    }


};

/**
 *
 * @param viewer
 * @param style relative by viewer container
 * @returns {THREE.InterfaceFrame}
 * @constructor
 *
 */
THREE.InterfaceFrame = function ( viewer, userData ) {

    var scope = this, coeff = 0.11, domElement;
    var style = userData.style;

    if ( userData.domElementId ) {
        domElement = $g( userData.domElementId );
    }

    init();

    function init () {
        var camera = new THREE.PerspectiveCamera( 70, window.innerWidth / window.innerHeight, 1, 1000 );
        camera.position.z = 45 / getStep();
        camera.position.y = 0;
        camera.position.x = 0;
        camera.target = new THREE.Vector3();

        var scene = new THREE.Scene();
        scene.add( camera );

        var light = new THREE.AmbientLight( 0xffffff );
        scene.add( light );


        var renderer = new THREE.WebGLRenderer( { antialias : true, alpha : true } );

        renderer.setPixelRatio( window.devicePixelRatio );

        var size = getSize();

        renderer.setSize( size.width, size.height );
//        renderer.setClearColor( 0xF0F0F0 );
        renderer.gammaInput = true;
        renderer.gammaOutput = true;

        var container;

        if ( domElement ) {
            container = domElement;
        } else {

            container = $c( 'div' );
            document.body.insertBefore( container, document.getElementById( 'toggle' ) );
        }


        container.appendChild( renderer.domElement );

        scope.container = container;
        scope.camera = camera;
        scope.scene = scene;
        scope.renderer = renderer;

        //

        window.addEventListener( 'resize', onWindowResize, false );


    }

    function onWindowResize () {

        if ( scope.renderer == null ) return;

        if ( domElement ) {
            resizeContainer();
        } else {
            setRelativeViewerContainer();
        }


    }

    function getStep () {

        var c1 = coeff / (45 / getSize().width);
        return (1 - c1) / 2 * c1 + c1;
    }

    function getSize () {

        return {
            width : domElement ? domElement.offsetWidth : (style.width || viewer.container.offsetWidth / 3),
            height : domElement ? domElement.offsetHeight : (style.height || viewer.container.offsetHeight / 3)
        }
    }

    function resizeContainer () {

        var size = getSize()
        scope.camera.aspect = size.width / size.height;
        scope.camera.updateProjectionMatrix();


        scope.camera.position.z = 45 / getStep();

        scope.renderer.setSize( size.width, size.height );

    }

    function setRelativeViewerContainer () {

        var width = style.width || viewer.container.offsetWidth / 3;
        var height = style.height || viewer.container.offsetHeight / 3;
        scope.camera.aspect = width / height;
        scope.camera.updateProjectionMatrix();


        scope.camera.position.z = 45 / getStep();

        scope.renderer.setSize( width, height );

        var offsetTop = viewer.container.offsetTop;
        var offsetLeft = viewer.container.offsetLeft;
        var offsetWidth = viewer.container.offsetWidth;
        var offsetHeight = viewer.container.offsetHeight;

        var containerTopInPercent = offsetTop / window.innerHeight * 100;
        var containerLeftInPercent = offsetLeft / window.innerWidth * 100;
        var containerBottomInPercent = (window.innerHeight - (offsetTop + offsetHeight)) / window.innerHeight * 100;
        var containerRightInPercent = (window.innerWidth - (offsetLeft + offsetWidth)) / window.innerWidth * 100;

        for ( var key in style ) {
            scope.container.style[ key ] = style[ key ];
        }

        scope.container.style.top = style.top ? (style.top + containerTopInPercent) + '%' : '';
        scope.container.style.left = style.left ? (style.left + containerLeftInPercent) + '%' : '';
        scope.container.style.right = style.right ? (style.right + containerRightInPercent) + '%' : '';
        scope.container.style.bottom = style.bottom ? (style.bottom + containerBottomInPercent) + '%' : '';
    }

    onWindowResize();

    return this;

};

/**
 * module for 3d objects animations
 * @param viewer
 * @param callback
 * @constructor
 */
THREE.AnimationsHelper = function ( viewer, callback ) {

    // main scene
    var container = viewer.container;
    var mainScene = viewer.scene;
    var mainCamera = viewer.camera;
    var controls = viewer.orbit_controls;
    var mainObjects = [];
    var hoverObjects = [];
    var objectsLookAtCamera = [];
    var eventGroups = {};

    this.dispose = function () {

        var interfaces = viewer.interfaces;
        var listeners = [ 'click', 'touchstart', 'mousemove' ];

        for ( var i = 0, j = interfaces.length; i < j; i++ ) {

            var frame = interfaces[ i ];

            listeners.map( function ( el ) {
                removeAllListeners( frame.container, el );
            } );

        }

        listeners.map( function ( el ) {
            removeAllListeners( container, el );
        } );

        removeAllListeners( controls, 'change' );


    };

    var _eventHandlers = {};

    function addListener ( node, event, handler, capture ) {
        if ( !(node in _eventHandlers) ) {
            _eventHandlers[ node ] = {};
        }
        if ( !(event in _eventHandlers[ node ]) ) {
            _eventHandlers[ node ][ event ] = [];
        }

        _eventHandlers[ node ][ event ].push( [ handler, capture ] );
        node.addEventListener( event, handler, capture );
    }

    function removeAllListeners ( node, event ) {
        if ( node in _eventHandlers ) {
            var handlers = _eventHandlers[ node ];
            if ( event in handlers ) {
                var eventHandlers = handlers[ event ];
                for ( var i = eventHandlers.length; i--; ) {
                    var handler = eventHandlers[ i ];
                    node.removeEventListener( event, handler[ 0 ], handler[ 1 ] );
                }
            }
        }
    }

    traverse( mainScene, mainObjects );

    // interfaces

    var interfaces = viewer.interfaces;

    for ( var i = 0, j = interfaces.length; i < j; i++ ) {

        var frame = interfaces[ i ];
        frame.objects = [];
        traverse( frame.scene, frame.objects );

        addListener( frame.container, 'click', onClick.bind( this, frame ), false );
        addListener( frame.container, 'touchstart', onTouchEnd.bind( this, frame ), false );
        addListener( frame.container, 'mousemove', onMouseMove.bind( this, frame ), false );

        //frame.container.addEventListener( 'click', onClick.bind( this, frame ), false );
        //frame.container.addEventListener( 'touchstart', onTouchEnd.bind( this, frame ), false );
        //frame.container.addEventListener( 'mousemove', onMouseMove.bind( this, frame ), false );


    }


    function traverse ( scene, array ) {

        (function addObjects ( children, pad ) {

            for ( var i = 0, l = children.length; i < l; i++ ) {

                var object = children[ i ];

                if ( object.children.length > 0 )
                    addObjects( object.children );

                if ( object.userData.animations ) {

                    checkAvailabilityGroups( object, scene );

                    array.push( object );
                    object.objectsLink = array;
                }

                if ( object.userData.lookat_camera ) {
                    object.lookAtCamera = true;
                    objectsLookAtCamera.push( object );
                    object.lookAt( mainCamera.position );
                }

                if ( object.userData.autorotation ) {
                    viewer.autorotations[ object.uuid ] = object;
                }


            }

        })( scene.children );

    }


    // object picking

    var raycaster = new THREE.Raycaster();
    var mouse = new THREE.Vector2();

    // events

    function getIntersects ( point, objects, camera ) {

        mouse.set( ( point.x * 2 ) - 1, -( point.y * 2 ) + 1 );

        raycaster.setFromCamera( mouse, camera );

        return raycaster.intersectObjects( objects );

    }

    var onUpPosition = new THREE.Vector2();
    var onHoverPosition = new THREE.Vector2();

    function getMousePosition ( dom, x, y ) {

        var rect = dom.getBoundingClientRect();
        return [ ( x - rect.left ) / rect.width, ( y - rect.top ) / rect.height ];

    }

    function handleClick ( objects, camera ) {

        var intersects = getIntersects( onUpPosition, objects, camera );

        if ( intersects.length > 0 ) {

            var object = intersects[ 0 ].object;

            if ( object.userData.animations.click && !isDisabled( object ) ) {
                applyAnimations( object, 'click', 0 );

                checkReverse( object );

            }
            if ( object.userData.animations.singleClick && !isDisabled( object ) ) {
                applySingleClickAnimation( object, 'single_click', 0 );

            }

        }


    }

    function handleHover ( objects, camera ) {


        var intersects = getIntersects( onHoverPosition, objects, camera );

        if ( intersects.length > 0 ) {

            var object = intersects[ 0 ].object;

            if ( object.userData.animations.hover && !isDisabled( object ) ) {
                applyAnimations( object, 'hover', 0 );
                applyBackHoverAnimation( object );
            }


        } else {

            applyBackHoverAnimation();
        }

    }


    function onControlsChanged () {

        if ( objectsLookAtCamera.length == 0 ) return;

        for ( var i = 0; i < objectsLookAtCamera.length; i++ ) {

            if ( objectsLookAtCamera[ i ].lookAtCamera )
                objectsLookAtCamera[ i ].lookAt( mainCamera.position );

        }

    }


    // events

    function onClick ( frame, event ) {

        var array = getMousePosition( frame.container, event.clientX, event.clientY );
        onUpPosition.fromArray( array );

        handleClick( frame.objects, frame.camera );


    }


    function onTouchEnd ( frame, event ) {

        var touch = event.changedTouches[ 0 ];

        var array = getMousePosition( frame.container, touch.clientX, touch.clientY );
        onUpPosition.fromArray( array );

        handleClick( frame.objects, frame.camera );

        //document.removeEventListener( 'touchend', onFrameTouchEnd.bind(this, frame), false );

    }

    function onMouseMove ( frame, event ) {

        var array = getMousePosition( frame.container, event.clientX, event.clientY );
        onHoverPosition.fromArray( array );
        handleHover( frame.objects, frame.camera );
    }

    var frame = { container : container, objects : mainObjects, camera : mainCamera };

    addListener( container, 'click', onClick.bind( this, frame ), false );
    addListener( container, 'touchstart', onTouchEnd.bind( this, frame ), false );
    addListener( container, 'mousemove', onMouseMove.bind( this, frame ), false );
    addListener( controls, 'change', onControlsChanged, false );

    //container.addEventListener( 'click', onClick.bind( this, frame ), false );
    //container.addEventListener( 'touchstart', onTouchEnd.bind( this, frame ), false );
    //container.addEventListener( 'mousemove', onMouseMove.bind( this, frame ), false );
    //controls.addEventListener( 'change', onControlsChanged, false );


    function checkAvailabilityGroups ( object, scene ) {

        var animations = object.userData.animations.click;

        if ( animations ) {

            for ( var i = 0; i < animations.length; i++ ) {

                if ( animations[ i ].event_id ) {

                    if ( !eventGroups[ animations[ i ].event_id ] ) {
                        eventGroups[ animations[ i ].event_id ] = [];
                    }

                    eventGroups[ animations[ i ].event_id ].push( scene.getObjectByProperty( 'name', animations[ i ].object, true ) );

                }

            }

        }

    }

    function checkReverse ( object ) {

        var reverse = object.userData.animations.reverse;

        if ( !reverse )
            object.userData.animations.reverse = true;
        else
            object.userData.animations.reverse = !object.userData.animations.reverse;

    }

    /*
     if click event array has some object with setting : sequence = true
     following object will be call by sequence
     example
     "click" :{
     {
     "object": "obj1",
     ...settings..
     },
     {
     "object": "obj2",
     ...settings..
     "sequence" : true
     },
     {
     "object": "obj3",
     ...settings..
     },
     {
     "object": "obj4",
     ...settings..
     },
     }
     obj1 and obj2 will be call at the same time
     obj3 and obj4 will be call at the same when obj2 has finished the animation (by duration)
     if for obj3 add setting: sequence = true obj4 will be call when that one has finished the animation (by duration)

     */

    function applyAnimations ( object, event, index ) {


        var animations = object.userData.animations[ event ];

        for ( var i = index; i < animations.length; i++ ) {

            var objs = getObj( animations[ i ].object );
            var sequenceBreak = false;

            objs.map( function ( obj ) {

                if ( !obj.userData[ event ] )
                    obj.userData[ event ] = {};
                obj.userData[ event ][ object.uuid + i ] = animations[ i ];


                switch ( event ) {
                    case 'click':
                        if ( animations[ i ].event === 'custom' ) {
                            obj.userData.animations.custom = animations[ i ];
                            applyCustomAnimation( obj );
                        } else if ( animations[ i ].event === 'autorotation' ) {
                            applyAutoRotation( obj );
                        } else if ( animations[ i ].event === 'camera_autorotate' ) {
                            applyControlsAutoRotation( animations[ i ].arguments );
                        } else if ( animations[ i ].event === 'scene_event' ) {
                            applySceneEvent( animations[ i ].arguments );
                        } else if ( animations[ i ].event === 'cycle_event' ) {
                            applyCycleEvent( obj, object.uuid + i );
                        } else if ( animations[ i ].event === 'single_change' ) {
                            applySingleChange( obj, object, i );
                        } else if ( animations[ i ].event === 'material_change' ) {
                            applyMaterialChange( obj, object.uuid + i );
						} else if ( animations[ i ].event === 'hide_object' ) {
                            applyHideChange( obj, object.uuid + i );
                        } else if ( animations[ i ].event === 'show_object' ) {
                            applyShowChange( obj, object.uuid + i );
                        } else if ( animations[ i ].event === 'toggle' ) {
                            applyToggle( obj, object.uuid + i );
                        } else if ( animations[ i ].event === 'reset' ) {
                            applyReset( obj, object.uuid + i );
                        } else
                            applyClickAnimation( obj, object.uuid + i );

                        break;
                    case 'hover':
                        applyHoverAnimation( obj, object.uuid + i );
                        break;
                }

                if ( animations[ i ].sequence ) {

                    obj.userData[ event ][ object.uuid + i ].sequence = {
                        callback : applyAnimations,
                        args : [ object, event, ++i ]
                    };
                    sequenceBreak = true;
                }

            } );


            if ( sequenceBreak )
                break;

        }

    }

    function getObj ( name ) {

        var objects = [];
        var objNames = name.split( ',' );

        objNames.map( function ( objName ) {

            objName = objName.trim();

            var obj = mainScene.getObjectByProperty( 'name', objName, true );

            if ( !obj ) {

                for ( var i = 0, j = interfaces.length; i < j; i++ ) {
                    obj = interfaces[ i ].scene.getObjectByProperty( 'name', objName, true );
                    if ( obj != undefined )
                        break;
                }

            }
            objects.push( obj );

        } )

        return objects;
    }

    function isDisabled ( object ) {

        return object.userData.animations.disabled;


    }

    function applyCustomAnimation ( object ) {

        var group = eventGroups[ object.name ];
        var action = object.userData.animations.custom.event_action;

        for ( var i = 0; i < group.length; i++ ) {

            var animation = group[ i ].userData.click;


            if ( !animation ) continue;

            for ( var key in animation ) {

                var current = animation[ key ];
                switch ( action ) {
                    case 'on':

                        if ( !current.active && !current.clickOn )
                            applyClickAnimation( group[ i ], key );

                        break;
                    case 'off':
                        if ( !current.active && current.clickOn )
                            applyClickAnimation( group[ i ], key );

                        break;

                    case 'other':
                        break;
                }

            }

        }

    }

    function applyAutoRotation ( object ) {

        object.userData.autorotation.enabled = !object.userData.autorotation.enabled;

    }

    function applyControlsAutoRotation ( options ) {

        viewer.cameraAnimationHelper.applyAutorotate( options );
    }

    function applyCameraAnimations ( viewName ) {
        viewer.cameraAnimationHelper.applyAnimations( viewName );
    }

    function applySceneEvent ( options ) {

        for ( var key in options ) {
            switch ( key ) {
                case 'full_screen':

                    if ( options.full_screen.switch )
                        THREEx.FullScreen.switch();

                    break;
            }
        }

    }


    function applyCycleEvent ( object, uuid ) {

        if ( object.userData.click[ uuid ].active ) return;

        // for start make simple click animation by arguments
        applyClickAnimation( object, uuid );

        var enabled = object.userData.click[ uuid ].cycle.enabled;

        if ( enabled ) {

            delete object.userData.cycle[ uuid ];

        } else {

            var steps = object.userData.click[ uuid ].cycle.steps;
            var interval = object.userData.click[ uuid ].cycle.interval;
            var duration = object.userData.click[ uuid ].cycle.duration;
            var easing = object.userData.click[ uuid ].cycle.easing;

            if ( !object.userData.cycle ) object.userData.cycle = {};

            object.userData.cycle[ uuid ] = [];

            for ( var i = 0, j = steps.length; i < j; i++ ) {

                var uuidStep = THREE.Math.generateUUID();
                object.userData.cycle[ uuid ].push( {
                    uuid : uuidStep,
                    arguments : steps[ i ],
                    delay : interval,
                    duration : duration,
                    easing : easing
                } );

            }

            makeStep( object, uuid, 0 );

        }


        object.userData.click[ uuid ].cycle.enabled = !enabled;
    }

    function makeStep ( object, uuid, i ) {

        if ( !object.userData.cycle[ uuid ] ) return;

        var step = object.userData.cycle[ uuid ][ i ];

        var index = i == object.userData.cycle[ uuid ].length - 1 ? 0 : i + 1;


        step.sequence = { callback : makeStep, args : [ object, uuid, index ] };
        object.userData.click[ step.uuid ] = step;

        applyClickAnimation( object, step.uuid, function () {
            step.sequence = undefined;
        } );

    }


    function applySingleChange ( object, parent, index ) {

        applyClickAnimation( object, parent.uuid + index );
        var toggle = object.userData.click[ parent.uuid + index ].toggle;

        var toggleObjects = getObj( toggle );

        toggleObjects.map( function ( toggleObject ) {

            if ( !toggleObject.userData.animations.click ) {
                var arguments = {};

                for ( var key in object.userData.click[ parent.uuid + index ].backAnimation ) {
                    arguments[ key ] = object.userData.click[ parent.uuid + index ].backAnimation[ key ].end;
                }

                toggleObject.userData.animations.click = [ {
                    "object" : object.userData.click[ parent.uuid + index ].object,
                    "event" : "single_change",
                    "toggle" : parent.name,
                    "arguments" : arguments,
                    "delay" : object.userData.click[ parent.uuid + index ].delay,
                    "duration" : object.userData.click[ parent.uuid + index ].duration,
                    "easing" : object.userData.click[ parent.uuid + index ].easing
                } ];

                toggleObject.userData.animations.hover[ 0 ].disabled = false;
            }

            parent.objectsLink.splice( parent.objectsLink.indexOf( parent ), 1 );

            if ( toggleObject.objectsLink.indexOf( toggleObject ) == -1 )
                toggleObject.objectsLink.push( toggleObject );

        } )


    }

    function applyMaterialChange ( object, uuid ) {

        var key = object.userData.click[ uuid ].arguments.material;
        viewer.setMaterial( key, object );

    }
	
	/// a bit shit
    function applyHideChange ( object, uuid ) {

        var key = object.userData.click[ uuid ];
		object.visible = false;
    }
	
    function applyShowChange ( object, uuid ) {

        var key = object.userData.click[ uuid ];
		object.visible = true;
    }

    function applyToggle ( object, uuid ) {

        object.userData.animations.disabled = object.userData.click[ uuid ].disabled;
    }

    function applyReset ( object, uuid ) {

        object.userData.click[ uuid ].delay = 0;
        object.userData.click[ uuid ].duration = 0;
        object.userData.click[ uuid ].easing = null;

        applyClickAnimation( object, uuid );
    }


    function applyClickAnimation ( object, uuid, cycleCallback ) {

        if ( object.userData.click[ uuid ].active && cycleCallback === undefined ) return;

        var arguments = object.userData.click[ uuid ].arguments;
        var delay = object.userData.click[ uuid ].delay;
        var duration = object.userData.click[ uuid ].duration;
        var easing = getCurve( object.userData.click[ uuid ].easing );
        var visible = object.userData.click[ uuid ].visible;
        var lookAtCamera = object.userData.click[ uuid ].lookat_camera;
        var eventCamera = object.userData.click[ uuid ].event_cam;
        var easingCamera = object.userData.click[ uuid ].easing_cam;
        var durationCamera = object.userData.click[ uuid ].duration_cam;
        var toggle = object.userData.click[ uuid ].toggle || object.userData.click[ uuid ].oneWay;
        var eventCameraAnimations = object.userData.click[ uuid ].event_cam_animations;


        if ( object.userData.click[ uuid ].clickOn && cycleCallback === undefined ) {
            applyAnimation( 'clickOff' + object.uuid + uuid, object, object.userData.click[ uuid ].backAnimation, duration, delay, easing, function () {

                object.userData.click[ uuid ].active = false;
                object.userData.click[ uuid ].clickOn = false;

                if ( visible != undefined )
                    object.visible = !visible;

                if ( lookAtCamera != undefined )
                    object.lookAtCamera = !lookAtCamera;

                if ( object.userData.click[ uuid ].sequence ) {

                    var args = object.userData.click[ uuid ].sequence.args;
                    object.userData.click[ uuid ].sequence.callback( args[ 0 ], args[ 1 ], args[ 2 ] );

                }

            } );


        } else {

            var args = getArguments( arguments, object );
            object.userData.click[ uuid ].backAnimation = args.back;

            catchInHover( object, args.forward );

            applyAnimation( 'clickOn' + object.uuid + uuid, object, args.forward, duration, delay, easing, function () {

                object.userData.click[ uuid ].active = false;
                object.userData.click[ uuid ].clickOn = true;

                if ( visible != undefined )
                    object.visible = visible;

                if ( lookAtCamera != undefined ) {

                    object.lookAtCamera = lookAtCamera;
                    object.lookAt( mainCamera.position );
                    if ( objectsLookAtCamera.indexOf( object ) == -1 )
                        objectsLookAtCamera.push( object )

                }
                if ( object.userData.click[ uuid ].sequence ) {

                    var args = object.userData.click[ uuid ].sequence.args;
                    object.userData.click[ uuid ].sequence.callback( args[ 0 ], args[ 1 ], args[ 2 ] );

                }

                if ( cycleCallback ) cycleCallback();

                if ( toggle ) {
                    object.userData.click[ uuid ].clickOn = false;
                    delete object.userData.click[ uuid ];
                }


            } );
        }

        if ( eventCamera ) {

            viewer.switchCamera( eventCamera, durationCamera, easingCamera );

        }

        if ( eventCameraAnimations ) {
            applyCameraAnimations( eventCameraAnimations );
        }


        object.userData.click[ uuid ].active = true;
    }

    function catchInHover ( object, args ) {

        if ( object.userData.hover ) {

            for ( var uuid in object.userData.hover ) {
                if ( object.userData.hover[ uuid ].afterAnimation ) {
                    if ( object.name === 'sprite_obj13' )
                        checkAndApplyArgs( object.userData.hover[ uuid ] );

                }

            }

        }

        function checkAndApplyArgs ( arguments ) {

            for ( var key in args ) {

                if ( arguments.backAnimation[ key ] )
                    arguments.backAnimation[ key ].obj = arguments.backAnimation[ key ].end = extend( {}, args[ key ].end );

            }

        }

    }

    function applySingleClickAnimation ( object, event, index ) {

        var animations = object.userData.animations.singleClick;

        for ( var i = 0, j = animations.length; i < j; i++ ) {

            animations[ i ].oneWay = true;

        }

        object.userData.animations.click = animations;


        applyAnimations( object, 'click', 0 );

    }


    function applyHoverAnimation ( object, uuid ) {

        if ( object.userData.hover[ uuid ].disabled || object.userData.hover[ uuid ].active || object.userData.hover[ uuid ].afterAnimation ) return;

        var arguments = object.userData.hover[ uuid ].arguments;
        var delay = object.userData.hover[ uuid ].delay;
        var duration = object.userData.hover[ uuid ].duration;
        var easing = getCurve( object.userData.hover[ uuid ].easing );
        var visible = object.userData.hover[ uuid ].visible;

        var args = getArguments( arguments, object );

        object.userData.hover[ uuid ].backAnimation = args.back;


        applyAnimation( 'hover' + object.uuid, object, args.forward, duration, delay, easing, function () {

            object.userData.hover[ uuid ].active = false;
            object.userData.hover[ uuid ].afterAnimation = true;

            if ( visible != undefined )
                object.visible = visible;

        } );

        object.userData.hover[ uuid ].active = true;

        hoverObjects.push( object );

    }

    function getArguments ( arguments, object ) {

        var backArgs = {};

        var forwardArgs = {};

        for ( var argument in arguments ) {

            var objectArgument = getDiffArgument( argument, object );


            if ( arguments[ argument ] instanceof Object ) {

                var obj = {};
                var end = {};

                for ( var key in arguments[ argument ] ) {
                    if ( key === 'x' || key === 'y' || key === 'z' || key === 'r' || key === 'g' || key === 'b' ) {

                        obj[ key ] = objectArgument[ key ];
                        end[ key ] = arguments[ argument ][ key ];
                    }


                }

            } else {

                if ( typeof  arguments[ argument ] != 'function' ) {
                    var obj = objectArgument;
                    var end = arguments[ argument ];
                }

            }

            forwardArgs[ argument ] = { obj : obj, end : end };

            backArgs[ argument ] = { obj : end, end : obj };

        }

        return {
            forward : forwardArgs, back : backArgs
        }
    }

    function applyBackHoverAnimation ( hoverObject ) {

        if ( hoverObjects.length == 0 ) return;

        var interval = setInterval( function () {

            for ( var i = 0; i < hoverObjects.length; i++ ) {

                var object = hoverObjects[ i ];

                if ( hoverObject && hoverObject === object ) continue;

                if ( object.userData.hover ) {

                    for ( var uuid in object.userData.hover ) {
                        if ( object.userData.hover[ uuid ].afterAnimation ) {

                            backHoverAnimation( object, uuid );

                            hoverObjects.splice( hoverObjects.indexOf( object ), 1 );
                        }

                    }

                }

            }

            if ( hoverObjects.length == 0 ) clearInterval( interval );

        }, 200 );


        function backHoverAnimation ( object, uuid ) {

            var args = object.userData.hover[ uuid ];
            var visible = object.userData.hover[ uuid ].visible;

            applyAnimation( 'backHover' + object.uuid, object, args.backAnimation, args.duration, args.delay, getCurve( args.easing ), function () {

                object.userData.hover[ uuid ].afterAnimation = false;

                if ( visible != undefined )
                    object.visible = !visible;

            } );

        }

    }

    function applyAnimation ( name, object, args, duration, delay, easing, endCallback ) {

        if ( easing == null ) {
            callback( name, args, duration, delay, easing, undefined, function () {
                endCallback();

                if ( Object.keys( args ).length > 0 ) {
                    setArguments( args, object, 'end' );

                }
            } );
        } else
            callback( name, args, duration, delay, easing, animate, endCallback );

        function animate ( currentArgs ) {

            setArguments( currentArgs, object, 'obj' );

        }


    }

    function setArguments ( arguments, object, keyW ) {

        for ( var arg in arguments ) {

            var objectArgument = getDiffArgument( arg, object );

            if ( arguments[ arg ][ keyW ] instanceof Object ) {

                for ( var key in arguments[ arg ][ keyW ] ) {

                    objectArgument[ key ] = arguments[ arg ][ keyW ][ key ];

                }
            } else {

                setParameter( arg, object, arguments[ arg ][ keyW ] )
            }

        }

    }


    function setParameter ( key, object, value ) {

        var array = key.split( '.' );

        if ( array.length > 1 ) {
            if ( array.length == 2 )
                object[ array[ 0 ] ][ array[ 1 ] ] = value;
            else if ( array.length == 3 )
                object[ array[ 0 ] ][ array[ 1 ] ][ array[ 2 ] ] = value;
            else if ( array.length == 4 )
                object[ array[ 0 ] ][ array[ 1 ] ][ array[ 2 ] ][ array[ 3 ] ] = value;
            else if ( array.length == 5 )
                object[ array[ 0 ] ][ array[ 1 ] ][ array[ 2 ] ][ array[ 3 ] ][ array[ 4 ] ] = value;


        } else {
            object[ key ] = value;
        }

    }


};

/**
 * module for camera animations
 * @param viewer
 * @constructor
 */
THREE.CameraAnimationHelper = function ( viewer ) {

    var controls = viewer.orbit_controls;
    var speed = 0.5;
    var animation = { play : false, currentFrame: 0 };
    controls.autoRotateSpeed = speed;
/* html button
    var autoRotation = document.getElementById( 'auto-rotation' );
    autoRotation.addEventListener( 'change', function () {
        if ( this.checked ) {
            controls.autoRotate = true;
        } else {
            controls.autoRotate = false;
            enableDamping();
        }
    } );
*/
    this.applyAnimations = function ( options ) {
        var view = options.view;
        var animations = viewer.cameraAnimations[ view ];
        var action = options.action;
        var fov = undefined;

        animation.endFrame = Infinity;

        animation.timeOut = options.timeOut;

        if ( options.fov && viewer.camera.fov != options.fov ) {
            animation.play = false;
            fov = options.fov;
            action = "play";
        }

        if( options.startFrame ){
            animation.play = false;
            animation.currentFrame = options.startFrame;
            fov = viewer.camera.fov;
            action = "play";
        }

        if( options.playUntilFrame ){
            animation.play = false;
            animation.endFrame = options.playUntilFrame;
            action = "play";
        }

        switch ( action ) {
            case "pause":
                animation.play = false;
                break;
            case "stop":
                animation.play = false;
                animation.currentFrame = 0;
                break;
            case "play":
                if( animation.play ) return;
                animation.play = true;
                setTimeout( function(){
                    switchCamera( animations.tracks, animation.currentFrame, animations.scale, fov);
                }, 10);

            default:


        }


    };

    this.applyOptions = function ( currentView ) {

        if ( currentView.userData ) {

            for ( var key in currentView.userData ) {
                switch ( key ) {
                    case 'autorotation':
                        this.applyAutorotate( currentView.userData.autorotation );
                        break;
                    case 'invert':
                        applyOptions( currentView.userData.invert, 'invert' );
                        break;
                    case 'parameters':
                        applyOptions( currentView.userData.parameters );
                        break;
                    default:
                        break;

                }
            }

        }

    };

    this.applyAutorotate = function ( options ) {

        controls.autoRotateSpeed = speed;

        if ( options.speed ) controls.autoRotateSpeed = options.speed;

        if ( options.switch ) {
            controls.autoRotate = !controls.autoRotate;
            autoRotation.checked = controls.autoRotate;

        } else {
            controls.autoRotate = options.enable;
            autoRotation.checked = options.enable;

        }

        if ( options.delay && options.enable ) {

            controls.autoRotate = false;

            setTimeout( function () {

                controls.autoRotate = true;

            }, options.delay );

        }


        enableDamping()


    }

    function enableDamping () {
        var enableDamping = controls.enableDamping;

        if ( !controls.autoRotate ) {
            controls.enableDamping = false;
            setTimeout( function () {
                controls.enableDamping = enableDamping;
            }, 10 );
        }
    }

    function applyOptions ( options, key ) {

        for ( var option in options ) {
            if ( key ) controls[ key ][ option ] = options[ option ];
            else controls[ option ] = options[ option ];
        }
    }

    function switchCamera ( tracks, index, scale, fov, duration, easing ){

        if( index == 0 || fov){

            animation.play = false;
            var track = tracks[ index++ ];
            var position = track.position;
            var t_position = track.t_position;
            var quaternion = track.quaternion;
            //console.log( "sw_t_position", t_position );

            var arguments = {};

            if ( position ) {
                arguments.position = { x : position[ 0 ] * scale, y : position[ 1 ] * scale, z : position[ 2 ] * scale };
            }
            
            if ( t_position ) {
                arguments.t_position = { x : t_position[ 0 ] * scale, y : t_position[ 1 ] * scale, z : t_position[ 2 ] * scale };
            }

            if ( quaternion ) {
                arguments.quaternion = {
                    x : quaternion[ 0 ] * scale,
                    y : quaternion[ 1 ] * scale,
                    z : quaternion[ 2 ] * scale,
                    w : quaternion[ 3 ] * scale
                };
            }

            if( fov ){
                arguments.fov = fov;
            }

            viewer.moveCamera ( 'switchCamera_' + index, arguments, duration, 0, easing || 'easeOutSine', function (){
                animation.play = true;
                playAnimations( tracks, index, scale, (+new Date) );
            });

        } else {
            playAnimations( tracks, index, scale, (+new Date) );
        }

    }

    function playAnimations ( tracks, index, scale, time ) {

        animation.currentFrame = index;

        var delay = 0;
        scale = scale || 1;

        if ( index > 0 ) {
            var step = tracks[ index ].t - tracks[ index - 1 ].t;

            var delta = step - ((+new Date) - time);

            if ( delta > 0 )
                delay = delta;
        }

        if(animation.timeOut){
            delay += animation.timeOut;
            animation.timeOut = undefined;
        }

        var track = tracks[ index++ ];
        var position = track.position;
        var t_position = track.t_position;
        var quaternion = track.quaternion;
        //console.log( "pl_t_position", t_position );


        var arguments = {};

        if ( position ) {
            arguments.position = { x : position[ 0 ] * scale, y : position[ 1 ] * scale, z : position[ 2 ] * scale };
        }
        
        if ( t_position ) {
            arguments.t_position = { x : t_position[ 0 ] * scale, y : t_position[ 1 ] * scale, z : t_position[ 2 ] * scale };
        }

        if ( quaternion ) {
            arguments.quaternion = {
                x : quaternion[ 0 ] * scale,
                y : quaternion[ 1 ] * scale,
                z : quaternion[ 2 ] * scale,
                w : quaternion[ 3 ] * scale
            };
        }

        viewer.animateCamera( 'view_animation_' + index, arguments, 0, delay, function () {

            if(animation.play){

                if(index < animation.endFrame){

                    if ( index < tracks.length ) {
                        playAnimations( tracks, index, scale, (+new Date) );
                    } else {
                        playAnimations( tracks, 0, scale, (+new Date) );
                    }

                } else {

                    animation.play = false;
                }

            }

        } );

    }
};

/**
 * camera animation data loader
 * @param viewer
 * @constructor
 */
THREE.CameraAnimationDataLoad = function ( viewer ) {

    var loader = new THREE.FileLoader();
    loader.load( viewer.options.camera_animations, function ( text ) {

        viewer.cameraAnimations = JSON.parse( text ).animations;

        loadData( 0 );

    } );

    function loadData ( index ) {

        var key = Object.keys( viewer.cameraAnimations )[ index++ ];

        viewer.cameraAnimations[ key ].tracks = [];

        //BEFORE: Not using the static server
        //loader.load( viewer.cameraAnimations[ key ].path, function ( data ) {

        //Using the static server
        var path = viewer.options.scene_assets_url + viewer.cameraAnimations[ key ].path;
        loader.load( path, function ( data ) {

            JSON.parse( data ).space.root.i.map( function ( object ) {

                if ( object.anim ) {
                    var length = 0;

                    if ( object.anim.pos && object.anim.pos.length > 0 ) {
                        length = object.anim.pos.length;
                    }
                    if ( object.anim.t_pos && object.anim.t_pos.length > 0 ) {
                        length = object.anim.t_pos.length;
                    }
                    if ( object.anim.rot && object.anim.rot.length > 0 ) {
                        length = length < object.anim.rot.length ? object.anim.rot.length : length;
                    }

                    for ( var i = 0; i < length; i++ ) {
                        var track = {};
                        if ( object.anim.pos && object.anim.pos[ i ] ) {

                            track.position = object.anim.pos[ i ].v;
                            track.t = object.anim.pos[ i ].t;
                        }                       
                        if ( object.anim.t_pos && object.anim.t_pos[ i ] ) {

                            track.t_position = object.anim.t_pos[ i ].v;
                            track.t = object.anim.t_pos[ i ].t;
                        }
                        if ( object.anim.rot && object.anim.rot[ i ] ) {

                            track.quaternion = object.anim.rot[ i ].v;
                            track.t = object.anim.rot[ i ].t;
                        }

                        viewer.cameraAnimations[ key ].tracks[ i ] = track;
                    }
                }
            } );

            if ( index < Object.keys( viewer.cameraAnimations ).length )
                loadData( index );

        } );


    }

}