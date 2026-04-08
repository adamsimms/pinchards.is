// Ion viewer modified for d17 (HQ viewer)

function IonVR ( options ) {
    var options = options || {};

    var defaults = {
        stats : false,
        debug : false,
        container : null,
        skybox : false,
		sky : false,
        lightprobe : true,
        lightprobe_tx : '',
        lightprobe_comp : '',
        lightprobeInt : 0.8,
		exposure : 1.5,
        skybox_tx : 'env_tex',
        always_render : false,
        scene : '', // url
        env : '', // url
		scene_assets_url : '',
		woo_tex_url : '',
		woo_3d_url : '',
        geom_buffer : false,
        camera : {
            position : {
                x : 0,
                y : 0,
                z : 100
            },
            fov : 35,
            target : {
                x : 0,
                y : 0,
                z : 0
            }
        },
        g_in : false,
        g_out : false,
		g_f : 2,
        phys_shading : false,
        rotate_left : 1.57, // rotate main group by y axis
        spin : true, // spin on switching frames
        spin_duration : 1000,
        zoom_factor : 10,
        set : null,
        ready : false,
        showStatus : true,
		audio : false,
		audio_track: null
    };
	
	// data values
    this.data = {
        param: {
			wind: {
				direction: 350,
				speed: 30
			},
			gust: {
				direction: 270,
				speed: 25
			},
			sun: {
				distance: 400,
				inclination: 0.5,
				azimuth: 0.55,
				intensity: 1.5
			},
			sky: {
				turbidity: 10,
				rayleigh: 2,
				luminance: 1,
				mieCoefficient: 0.005,
				mieDirectionalG: 0.8,
				res: 2048
			},
			live_data: {
				current: {
					cloud: 0,
					feelslike_c: -2.9,
					feelslike_f: 26.7,
					gust_kph: 15.8,
					gust_mph: 9.8,
					humidity: 55,
					is_day: 1,
					last_updated: "1999-01-09 10:40",
					last_updated_epoch: null,
					precip_in: 0,
					precip_mm: 0,
					pressure_in: 30.4,
					pressure_mb: 1013,
					temp_c: 1,
					temp_f: 33.8,
					uv: 2,
					vis_km: 14,
					vis_miles: 8,
					wind_degree: 180,
					wind_dir: "NW",
					wind_kph: 50,
					wind_mph: 6.9
				}
			},
			live_astro: {
				astronomy: {
					astro: {
						moon_illumination: 63,
						moon_phase: "First Quarter",
						moonrise: "01:40 PM",
						moonset: "02:21 AM",
						sunrise: "07:50 AM",
						sunset: "04:45 PM"
					}
				}
			},
			animation: true,
			wind_factor: 2.4,
			sway_factor: 0.3,
			swiv_factor: 1.0,
			months : { 
				01: "January",
				02: "February", 
				03: "March", 
				04: "April", 
				05: "May", 
				06: "June", 
				07: "July", 
				08: "August", 
				09: "September", 
				10: "October", 
				11: "Novermber", 
				12: "December"
			},
			mMat: 0,
			cMat: 
				{
				0:0,
				1:0,
				2:0,
				3:0,
				4:0,
				5:0,
				6:0,
				7:0,
				8:0, //logo
				9:0,
				10:0,
				11:0,
				12:0,
				13:0
			}
		}
	};	
	
	// data values
    this.data_stored = {
        param: {
			wind: {
				direction: 350,
				speed: 30
			},
			gust: {
				direction: 270,
				speed: 25
			},
			sun: {
				distance: 400,
				inclination: 0.5,
				azimuth: 0.55,
				intensity: 1.5
			},
			sky: {
				turbidity: 10,
				rayleigh: 2,
				luminance: 1,
				mieCoefficient: 0.005,
				mieDirectionalG: 0.8,
				res: 2048
			},
			live_data: {
				current: {
					cloud: 0,
					feelslike_c: -2.9,
					feelslike_f: 26.7,
					gust_kph: 15.8,
					gust_mph: 9.8,
					humidity: 55,
					is_day: 1,
					last_updated: "1999-01-09 10:40",
					last_updated_epoch: null,
					precip_in: 0,
					precip_mm: 0,
					pressure_in: 30.4,
					pressure_mb: 1013,
					temp_c: 1,
					temp_f: 33.8,
					uv: 2,
					vis_km: 14,
					vis_miles: 8,
					wind_degree: 180,
					wind_dir: "NW",
					wind_kph: 50,
					wind_mph: 6.9
				}
			},
			live_astro: {
				astronomy: {
					astro: {
						moon_illumination: 63,
						moon_phase: "First Quarter",
						moonrise: "01:40 PM",
						moonset: "02:21 AM",
						sunrise: "07:50 AM",
						sunset: "04:45 PM"
					}
				}
			},
			animation: true,
			wind_factor: 0.4,
			sway_factor: 0.4,
			swiv_factor: 0.4,
			months : { 
				01: "January",
				02: "February", 
				03: "March", 
				04: "April", 
				05: "May", 
				06: "June", 
				07: "July", 
				08: "August", 
				09: "September", 
				10: "October", 
				11: "Novermber", 
				12: "December"
			},
			mMat: 0,
			cMat: 
				{
				0:0,
				1:0,
				2:0,
				3:0,
				4:0,
				5:0,
				6:0,
				7:0,
				8:0, //logo
				9:0,
				10:0,
				11:0,
				12:0,
				13:0
			}
		}
	};	

		
    this.options = extend( {}, defaults, options );

    this.animations = {};
    this.autorotations = {};
    this.interfaces = [];
    this.clock = new THREE.Clock();
    this.loader = new THREE.SceneLoader();
    this.loader.byRequest = true;
    this.cameraAnimations = {};
	
    this.preloader = this.options.preloader;
	
    // Initialization start
    if ( Detector.webgl ) {

        this.init();
    } else {
        Detector.addGetWebGLMessage();
    }
}


IonVR.prototype = {
    constructor : IonVR,

    init : function () {
		THREE.Cache.enabled = true;
        this.initContainer();
        //this.loadCameraAnimations();
        this.loadScene( function () {
            this.initCamera();
            this.initRenderer();
            this.initWater();
			this.fetchWeather();
			
            if ( this.onReady ) {
                this.onReady();
				this.updateSun();
            }
			
			if (ion.options.audio == true) {
					ion.initAudio();
				}
			
            this.start();
			// this.updateSun();
			
			setTimeout(loadDef, 2800);
			setTimeout(loadEnd, 6000);
			
			function loadDef() {
				ion.preloader.stop();
				ion.animateParamToSMP2('color', ion.sc.materials.m_cam, { opacity: -0.1 }, 2800, easeInOutSine);
				ion.camera.getObjectByName("audioMain").gain.context.resume();
			}			
			function loadEnd() {
				//ion.spinRound();
				ion.sc.objects.cam_mask.visible=false;
				if (ion.data.param.animation !== false) {
				ion.animateHouse();
				} else {}
				
			}
        } );
    },

    log : function () {
        if ( this.options.debug && window.console ) {
            console.log( arguments );
        }
    },

    loadScene : function ( callback ) {
        var _this = this;

        this.showStatus( 'Loading scene...' );

        var scene_loader = this.loader;
        //scene_loader.byRequest = true;

        scene_loader.callbackProgress = function ( progress ) {
            _this.showStatus( 'Loading... Models ' + progress.loadedModels + '/' + progress.totalModels + ', textures ' + progress.loadedTextures + '/' + progress.totalTextures );
            _this.preloader.updateProgress( progress );
        };

        if ( THREE.IONLoader ) {
            scene_loader.addGeometryHandler( "ion", THREE.IONLoader );
        }

        this.log( 'load scene', this.options.scene );

        // var path = (this.options.scene_assets_url || '') + this.options.scene;
        var path = this.options.scene;
        scene_loader.load( path, function ( sc, b, c ) {
            _this.log( 'load complete', sc, b, c );

            // _this.preloader.stop();

            _this.initScene( sc );
			ion.options.ready = true;


            if ( _this.options.debug ) {
                if ( typeof IonSceneExplorer != 'undefined' ) {
                    _this.explorer = new IonSceneExplorer( _this, sc );
                }
            }
            if ( callback ) {
                callback.call( _this );
            }
        } );

    },

    loadCameraAnimations : function () {

        new THREE.CameraAnimationDataLoad( this );

    },

    loadSecondaryScene : function ( callback ) {

        var _this = this;

        _this.preloader.start();

        this.showStatus( 'Loading scene...' );

        var scene_loader = new THREE.SceneLoader();

        scene_loader.callbackProgress = function ( progress ) {
            _this.showStatus( 'Loading... Models ' + progress.loadedModels + '/' + progress.totalModels + ', textures ' + progress.loadedTextures + '/' + progress.totalTextures );
            _this.preloader.updateProgress( progress );
        };

        if ( THREE.IONLoader ) {
            scene_loader.addGeometryHandler( "ion", THREE.IONLoader );
        }

        this.log( 'load secondary scene', this.options.secondary_scene );
        scene_loader.load( this.options.secondary_scene, function ( sc, b, c ) {
            _this.log( 'load secondary complete', sc, b, c );

            _this.preloader.stop();
            _this.hideStatus();

            _this.initSecondaryScene( sc );

            if ( callback ) {
                callback.call( _this );
            }
        } );

    },

    setMaterial : function ( key, object ) {

        var scope = this;

        this.preloader.start();
        this.loader.callbackProgress = function ( progress ) {
            scope.showStatus( 'Loading... Textures ' + progress.loadedTextures + '/' + progress.totalTextures );
            scope.preloader.updateProgress( progress );
        };

        this.loader.newMaterial( key, function () {
            object.material = scope.sc.materials[ key ];

            scope.applyAnimationToNewMaterial( object );
            //scope.spinRound();
			scope.preloader.stop();
            scope.hideStatus();
			scope.requestRender();
			
        } )
    },

    showCamInfo : function () {

        if(this.orbit_controls == null) return;

        if ( !this.cam_info ) {
            this.cam_info = $c( 'div' );
            this.cam_info.id = 'camera-info';
            this.container.appendChild( this.cam_info );
        }

        this.cam_info.innerHTML =
            '"position": [' +
            '' + round( this.camera.position.x, 100 ) + ',' +
            '' + round( this.camera.position.y, 100 ) + ',' +
            '' + round( this.camera.position.z, 100 ) + '],<br />' +
            '"target": [' +
            '' + round( this.orbit_controls.target.x, 100 ) + ', ' +
            '' + round( this.orbit_controls.target.y, 100 ) + ', ' +
            '' + round( this.orbit_controls.target.z, 100 ) + '],<br />' +
            '"fov": ' + round( this.camera.fov, 10 ) + '';
        ;

    },

    showLiveWeather : function () {

        if ( !this.weather_info ) {
            this.weather_info = $c( 'div' );
            this.weather_info.id = 'weather-info';
			// this.weather_info.style.z-index = 105;
            this.container.appendChild( this.weather_info );
			
			this.weather_in = $c( 'div' );
            this.weather_in.id = 'weather-in';
			this.weather_in.onclick = ion.showUI;
			// this.weather_in.style.z-index = 100;
            this.container.appendChild( this.weather_in );
			
			this.weather_bt = $c( 'div' );
            this.weather_bt.id = 'weather-bt';
			this.weather_bt.onclick = ion.hideUI;
			// this.weather_bt.style.z-index = 110;
            this.weather_info.appendChild( this.weather_bt );
			
            this.weather_dot = $c( 'div' );
            this.weather_dot.id = 'weather-dot';
			// this.weather_dot.style.z-index = 110;
            this.weather_info.appendChild( this.weather_dot );
			
            this.weather_text = $c( 'div' );
            this.weather_text.id = 'weather-text';
			// this.weather_text.style.z-index = 110;
            this.weather_info.appendChild( this.weather_text );
			
			
        };
		
		var currentDate = this.data.param.live_data.current.last_updated;
		var cTime = new Date(currentDate).toLocaleTimeString();
		var cDay = currentDate.substring(8, 10);
		var cM = currentDate.substring(5, 7);
		var month = ion.data.param.months[01].toString();
		
		// console.log(cM, MMMM);
		
		var iconW = new DOMParser().parseFromString('<svg height="20" viewBox="0 0 30 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3.50001 6.75H18.125C18.8419 6.75201 19.5407 6.52573 20.1203 6.10395C20.7 5.68217 21.1302 5.0868 21.3488 4.40409C21.5674 3.72139 21.5629 2.98683 21.336 2.30684C21.1092 1.62685 20.6717 1.03676 20.0869 0.62207C19.5022 0.207381 18.8007 -0.0103669 18.0839 0.000379477C17.3672 0.0111259 16.6724 0.249808 16.1004 0.681841C15.5284 1.11387 15.1088 1.71681 14.9024 2.4033C14.696 3.08978 14.7136 3.82415 14.9525 4.49999H3.50001C3.20164 4.49999 2.91549 4.61852 2.70451 4.8295C2.49353 5.04048 2.37501 5.32663 2.37501 5.62499C2.37501 5.92336 2.49353 6.20951 2.70451 6.42049C2.91549 6.63147 3.20164 6.75 3.50001 6.75ZM17 3.37499C17 3.15249 17.066 2.93498 17.1896 2.74997C17.3132 2.56497 17.4889 2.42077 17.6945 2.33563C17.9001 2.25048 18.1263 2.2282 18.3445 2.27161C18.5627 2.31502 18.7632 2.42216 18.9205 2.5795C19.0779 2.73683 19.185 2.93729 19.2284 3.15551C19.2718 3.37374 19.2495 3.59994 19.1644 3.80551C19.0792 4.01108 18.9351 4.18678 18.75 4.3104C18.565 4.43401 18.3475 4.49999 18.125 4.49999C17.8267 4.49999 17.5405 4.38147 17.3295 4.17049C17.1186 3.95951 17 3.67336 17 3.37499ZM25.875 4.37499C25.3359 4.37651 24.805 4.50716 24.3267 4.75601C23.8484 5.00485 23.4367 5.36466 23.1261 5.8053C22.8154 6.24594 22.6149 6.75459 22.5412 7.28866C22.4675 7.82274 22.5228 8.3667 22.7025 8.875H1.125C0.826633 8.875 0.540484 8.99353 0.329505 9.2045C0.118527 9.41548 0 9.70163 0 10C0 10.2984 0.118527 10.5845 0.329505 10.7955C0.540484 11.0065 0.826633 11.125 1.125 11.125H25.875C26.7701 11.125 27.6286 10.7694 28.2615 10.1365C28.8945 9.50355 29.25 8.6451 29.25 7.75C29.25 6.85489 28.8945 5.99644 28.2615 5.36351C27.6286 4.73057 26.7701 4.37499 25.875 4.37499ZM25.875 8.875C25.6525 8.875 25.435 8.80902 25.25 8.6854C25.065 8.56178 24.9208 8.38608 24.8357 8.18052C24.7505 7.97495 24.7282 7.74875 24.7717 7.53052C24.8151 7.31229 24.9222 7.11184 25.0795 6.9545C25.2369 6.79717 25.4373 6.69002 25.6556 6.64661C25.8738 6.6032 26.1 6.62548 26.3056 6.71063C26.5111 6.79578 26.6868 6.93997 26.8104 7.12498C26.9341 7.30998 27 7.52749 27 7.75C27 8.04837 26.8815 8.33451 26.6705 8.54549C26.4596 8.75647 26.1734 8.875 25.875 8.875ZM21.375 13.25H2.25C1.95163 13.25 1.66549 13.3685 1.45451 13.5795C1.24353 13.7905 1.125 14.0766 1.125 14.375C1.125 14.6734 1.24353 14.9595 1.45451 15.1705C1.66549 15.3815 1.95163 15.5 2.25 15.5H18.2025C17.9636 16.1758 17.946 16.9102 18.1524 17.5967C18.3588 18.2832 18.7784 18.8861 19.3504 19.3182C19.9224 19.7502 20.6172 19.9889 21.3339 19.9996C22.0507 20.0104 22.7522 19.7926 23.337 19.3779C23.9217 18.9632 24.3592 18.3731 24.586 17.6932C24.8129 17.0132 24.8174 16.2786 24.5988 15.5959C24.3802 14.9132 23.95 14.3178 23.3703 13.896C22.7907 13.4743 22.0919 13.248 21.375 13.25ZM21.375 17.75C21.1525 17.75 20.935 17.684 20.75 17.5604C20.565 17.4368 20.4208 17.2611 20.3357 17.0555C20.2505 16.85 20.2282 16.6238 20.2716 16.4055C20.3151 16.1873 20.4222 15.9868 20.5795 15.8295C20.7369 15.6722 20.9373 15.565 21.1556 15.5216C21.3738 15.4782 21.6 15.5005 21.8055 15.5856C22.0111 15.6708 22.1868 15.815 22.3104 16C22.4341 16.185 22.5 16.4025 22.5 16.625C22.5 16.9234 22.3815 17.2095 22.1705 17.4205C21.9595 17.6315 21.6734 17.75 21.375 17.75Z" fill="black" fill-opacity="0.74"/></svg>' , 'application/xml');
		
		var iconD = new DOMParser().parseFromString('<svg height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M11 0C4.93263 0 0 4.93263 0 11C0 17.0674 4.93263 22 11 22C17.0674 22 22 17.0674 22 11C22 4.93263 17.0674 0 11 0ZM11 19.8695C6.11368 19.8695 2.13053 15.8863 2.13053 11C2.13053 6.11368 6.11368 2.13053 11 2.13053C15.8863 2.13053 19.8695 6.11368 19.8695 11C19.8695 15.8863 15.8863 19.8695 11 19.8695Z" fill="black" fill-opacity="0.74"/><path d="M14.7286 5.9053L9.24013 8.29056C8.80013 8.47582 8.45276 8.82319 8.2675 9.26319L5.90539 14.7285C5.53487 15.5853 6.41487 16.4653 7.27171 16.0948L12.7601 13.7095C13.2001 13.5242 13.5475 13.1769 13.7328 12.7369L16.0949 7.27161C16.4886 6.39161 15.6086 5.51161 14.7286 5.9053ZM11.9728 11.9727C11.4401 12.5053 10.5833 12.5053 10.0507 11.9727C9.51803 11.44 9.51803 10.5832 10.0507 10.0506C10.5833 9.51793 11.4401 9.51793 11.9728 10.0506C12.5054 10.56 12.5054 11.44 11.9728 11.9727Z" fill="black" fill-opacity="0.74"/></svg>' , 'application/xml');
		
		var iconB = new DOMParser().parseFromString('<svg width="20" height="20" viewBox="0 0 20 20" fill="black" xmlns="http://www.w3.org/2000/svg"><path d="M11.1785 10L13.831 12.6525C14.156 12.9775 14.1579 13.5025 13.8302 13.8302C13.5047 14.1556 12.9772 14.1557 12.6525 13.831L10 11.1785L7.34747 13.831C7.02252 14.156 6.49754 14.1579 6.16984 13.8302C5.8444 13.5047 5.84427 12.9772 6.16896 12.6525L8.82149 10L6.16896 7.34747C5.84401 7.02252 5.84213 6.49754 6.16984 6.16984C6.49528 5.8444 7.02278 5.84427 7.34747 6.16896L10 8.82149L12.6525 6.16896C12.9775 5.84401 13.5025 5.84213 13.8302 6.16984C14.1556 6.49528 14.1557 7.02278 13.831 7.34747L11.1785 10ZM10 20C4.47715 20 0 15.5228 0 10C0 4.47715 4.47715 0 10 0C15.5228 0 20 4.47715 20 10C20 15.5228 15.5228 20 10 20ZM10 18.3333C14.6024 18.3333 18.3333 14.6024 18.3333 10C18.3333 5.39763 14.6024 1.66667 10 1.66667C5.39763 1.66667 1.66667 5.39763 1.66667 10C1.66667 14.6024 5.39763 18.3333 10 18.3333Z"  fill-opacity="0.5"/></svg>' , 'application/xml');
		
		var iconI = new DOMParser().parseFromString('<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 0C5.3728 0 0 5.3728 0 12C0 18.6272 5.3728 24 12 24C18.6272 24 24 18.6272 24 12C24 5.3728 18.6272 0 12 0ZM10.6133 17.1797V11.6864C10.6133 11.3186 10.7594 10.9659 11.0195 10.7059C11.2795 10.4458 11.6322 10.2997 12 10.2997C12.3678 10.2997 12.7205 10.4458 12.9805 10.7059C13.2406 10.9659 13.3867 11.3186 13.3867 11.6864V17.1797C13.3867 17.5475 13.2406 17.9002 12.9805 18.1603C12.7205 18.4203 12.3678 18.5664 12 18.5664C11.6322 18.5664 11.2795 18.4203 11.0195 18.1603C10.7594 17.9002 10.6133 17.5475 10.6133 17.1797ZM12 8.78667C11.6704 8.78667 11.3481 8.68892 11.074 8.50578C10.8 8.32265 10.5863 8.06235 10.4602 7.75781C10.3341 7.45326 10.301 7.11815 10.3654 6.79485C10.4297 6.47155 10.5884 6.17458 10.8215 5.94149C11.0546 5.7084 11.3515 5.54967 11.6748 5.48536C11.9982 5.42105 12.3333 5.45405 12.6378 5.5802C12.9423 5.70635 13.2026 5.91997 13.3858 6.19405C13.5689 6.46813 13.6667 6.79036 13.6667 7.12C13.6667 7.56203 13.4911 7.98595 13.1785 8.29851C12.866 8.61107 12.442 8.78667 12 8.78667Z" fill="white"/></svg>' , 'application/xml');
		
        this.weather_dot.innerHTML =
		'●';
        this.weather_text.innerHTML =
		'&nbsp&nbsp' + 'Atlantic Ocean, ' + month + ' ' + cDay + ' at ' + cTime + 
		'&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp';
	
		this.weather_text.appendChild(iconW.documentElement);
		
		this.weather_text.innerHTML +=
		'&nbsp&nbsp' + round( this.data.param.live_data.current.wind_kph, 100 ) + ' km/h' +
		'&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp';
		
		this.weather_text.appendChild(iconD.documentElement);
		
		this.weather_text.innerHTML +=
		'&nbsp&nbsp' + round( this.data.param.live_data.current.wind_degree, 100 ) + 'º' + ' ' + ion.data.param.live_data.current.wind_dir;
		
        this.weather_bt.innerHTML =
		'';
		// this.weather_bt.appendChild(iconB.documentElement);
		
        this.weather_in.innerHTML =
		'';
		// this.weather_in.appendChild(iconI.documentElement);

	
    },
	
	showUI : function() {
		document.getElementById('weather-info').style.bottom="0%"
	},
	
	hideUI : function() {
		document.getElementById('weather-info').style.bottom="-10%"
	},

    initScene : function ( sc ) {

        this.separateInterfaceGroup( sc );

        this.sc = sc;

        this.scene = sc.scene;
        this.main_group = this.scene.getObjectByName( 'main_group' );


        for ( var i in this.main_group.children ) {
            var obj = this.main_group.children[ i ];

            if ( obj instanceof THREE.Mesh && obj.userData ) {
                if ( obj.userData.sub_div ) {
                    var sub_div = obj.userData.sub_div;
                    this.log( 'sub div', obj.name, sub_div );
                    this.showStatus( 'Subdividing ' + obj.name );

                    var modifier = new THREE.SubdivisionModifier( sub_div );
                    modifier.modify( obj.geometry );
                }

                if ( obj.userData.comp_vn ) {
                    this.log( 'comp vn', obj.name );
                    this.showStatus( 'Computing normals ' + obj.name );
                    obj.geometry.computeVertexNormals();
                }

                if ( obj.userData.comp_fn ) {
                    this.log( 'comp fn', obj.name );
                    this.showStatus( 'Computing face normals ' + obj.name );
                    obj.geometry.computeFaceNormals();
                }

                if ( obj.userData.comp_cn ) {
                    this.log( 'comp cn', obj.name );
                    this.showStatus( 'Computing centroids ' + obj.name );
                    obj.geometry.computeCentroids();
                }

                if ( obj.userData.buffer && this.options.geom_buffer && THREE.BufferGeometryUtils ) {
                    delete obj.geometry.__tmpVertices; // bug fix, may also use clone
                    //obj.geometry = obj.geometry.clone();

                    this.showStatus( 'Triangulating ' + obj.name );
                    THREE.GeometryUtils.triangulateQuads( obj.geometry );

                    this.showStatus( 'Buffering ' + obj.name );
                    var new_geo = THREE.BufferGeometryUtils.fromGeometry( obj.geometry );
                    obj.geometry.dispose();
                    obj.geometry = new_geo;

                    //obj.material = this.sc.materials.wire_orange;
                }
            }
        }

        this.log( 'main_group', this.main_group );

        this.main_group.rotation.y = this.options.rotate_left;
		this.log('init_rotation',this.options.rotate_left)


        // skybox
        if ( this.options.skybox ) {
            var shader = THREE.ShaderLib[ "cube_y" ]; // vertical offset cube shader
            shader.uniforms[ "tCube" ].value = this.sc.textures[ this.options.skybox_tx ];
            var material = new THREE.ShaderMaterial( {
                    fragmentShader : shader.fragmentShader,
                    vertexShader : shader.vertexShader,
                    uniforms : shader.uniforms,
                    depthWrite : false,
                    side : THREE.BackSide
                } ),

            skybox = new THREE.Mesh( new THREE.CubeGeometry( 10000, 10000, 10000 ), material );
            skybox.position.set( 0, 0, 0 );
            //this.scene.add( skybox );
			this.scene.background = this.sc.textures[this.options.skybox_tx];
        }
		
        // sky
        if ( this.options.sky ) {
		
			var sunLight = new THREE.DirectionalLight( 0xfeeacc, this.data.param.sun.intensity );
			sunLight.position.set(380, 4.3, 123);
			sunLight.name = 'sunLight';
			
            var sky = new THREE.Sky();
			sky.name = 'sky';
			var uniforms = sky.material.uniforms;

			uniforms[ 'turbidity' ].value = this.data.param.sky.turbidity;
			uniforms[ 'rayleigh' ].value = this.data.param.sky.rayleigh;
			uniforms[ 'luminance' ].value = this.data.param.sky.luminance;
			uniforms[ 'mieCoefficient' ].value = this.data.param.sky.mieCoefficient;
			uniforms[ 'mieDirectionalG' ].value = this.data.param.sky.mieDirectionalG;

			var cubeCamera = new THREE.CubeCamera( 0.1, 1, this.data.param.sky.res );
			cubeCamera.renderTarget.texture.generateMipmaps = true;
			cubeCamera.renderTarget.texture.minFilter = THREE.LinearMipMapLinearFilter;
			cubeCamera.name = 'cubeCamera';

			this.scene.background = cubeCamera.renderTarget;
			this.scene.add( sunLight );
			this.scene.add( cubeCamera );
			this.scene.add( sky );
			ion.sc.objects.house.material[0].envMap = 
			ion.sc.objects.house.material[1].envMap = 
			ion.sc.objects.house.material[2].envMap = cubeCamera.renderTarget.texture;
        }
		
		if ( this.options.lightprobe ) {
			
			var lightProbe = new THREE.LightProbe();
			lightProbe.name = 'lightProbe';
			this.scene.add( lightProbe );
			if (this.options.lightprobe_comp == true ) {
				lightProbe.copy( THREE.LightProbeGenerator.fromCubeTexture( this.sc.textures[ this.options.lightprobe_tx ] ) );
			} else {};
			lightProbe.intensity =  this.options.lightprobeInt;
			
			if (this.options.lightprobe_set == "env_cube1") {
			// city bg sh
			lightProbe.sh.fromArray([
				0.21988952323971958, 
				0.353873883080967, 
				0.5979957196333927, 
				0.06558677467388137, 
				0.168535799494315, 
				0.3727295821407715, 
				-2.838907723444506e-14, 
				-3.954370089901607e-14, 
				-4.022441719324286e-14, 
				2.838929245680154e-14, 
				3.954331642208093e-14, 
				4.0224862808547535e-14, 
				-1.0201367426154214e-15, 
				5.862222392184944e-15, 
				-6.706257573915519e-15, 
				1.0201908564157353e-15, 
				-5.861917556646891e-15, 
				6.7057196117202916e-15, 
				0.09217011442613726, 
				0.10671077188574228, 
				0.11792560917973822, 
				-4.880465721524953e-19, 
				9.756262751934876e-19, 
				-4.760480508502691e-18, 
				0.15964303814943762, 
				0.18482815100295383, 
				0.20425278456377272]);
			} else if (this.options.lightprobe_set == "studio1") {
			//studio sh
			lightProbe.sh.fromArray([
				0.029401531146369564, 
				0.030393056150284967, 
				0.03882441978503216, 
				-0.006532247813053846, 
				-0.006830553369357048, 
				-0.00876550792363131, 
				0.0054872013622510795, 
				0.005761665763592089, 
				0.007102911675978676, 
				0.001051650901536813, 
				0.0008596636445337802, 
				0.0006091165567122502, 
				-0.0036529445742401667, 
				-0.003961831226970255, 
				-0.005260862974352544, 
				-0.004670203370822745, 
				-0.005086150563277629, 
				-0.0060334392731383295, 
				-0.0005266474551182757, 
				-0.0005770949473494129, 
				-0.0006364091203026864, 
				0.0027920785910252243, 
				0.00304628013386876, 
				0.004018038061411669, 
				0.0005419559484224849, 
				0.0005783680671260237, 
				0.0008420237221743866
				]);
			} else if (this.options.lightprobe_set == "ext1") {
			//ext1
			lightProbe.sh.fromArray([
				1.0406571709113295, 
				1.161886628443305, 
				1.3063027226917772, 
				-0.6490791656527983, 
				-0.49548467256667383, 
				-0.31325949844248935, 
				0.04381565686615428, 
				0.06532099855040499, 
				0.07807386785800434, 
				0.08630908113628857, 
				-0.10730147835215309, 
				-0.10939680130133521, 
				-0.22306053341686657, 
				-0.2557966190958097, 
				-0.2506786037181489, 
				0.020464629184951535, 
				0.028274316528776787, 
				0.0298683696140515, 
				-0.09474396955458116, 
				-0.10071400468444641, 
				-0.11257025430936203, 
				-0.040398956433938614, 
				-0.044402750362525285, 
				-0.04342718627203005, 
				0.14164765158865514, 
				0.15794017769014346, 
				0.12746705307744025
				]);
			} else {}
		}
		
		this.o = this.sc.objects;
		this.m = this.sc.materials;
		this.t = this.sc.textures;
		this.c = this.sc.cameras;
		this.pc = this.data.param;
    },
	
	updateSun : function() {
	
		var cTime = new Date(ion.data.param.live_data.current.last_updated).getHours();
		var cMin = new Date(ion.data.param.live_data.current.last_updated).getMinutes();
		
		var cHour = cTime + (cMin * 0.016666);
		var aziF = 0.5 + (cHour-7)/22;
		
		if ( aziF < 0.55 ) {
			var azi = 0.55;
		} else if ( aziF >= 0.55 && aziF <= 0.95) {
			var azi = aziF;
		} else if ( aziF > 0.95 ) {
			var azi = 0.95;
		} 
		
		if (cHour < 12 ) {
			var inc = cHour/15;
		} else if (cHour >= 12 ) {
			var inc = 0.75-((cHour-12)/17);
		} else {
			//var inc = 0.3
		}
		
		this.data.param.sun.inclination = inc;
		this.data.param.sun.azimuth = azi;
		
		// var inc = this.data.param.sun.inclination;
		// var azi = this.data.param.sun.azimuth;
		
		//console.log('inc:', inc, 'azi:', azi)
	
		var sunLightparam = this.data.param.sun;
		var sunLight = this.scene.getObjectByName('sunLight');
		var cubeCamera = this.scene.getObjectByName('cubeCamera');
		var sky = this.scene.getObjectByName('sky');
		
		var theta = Math.PI * ( inc - 0.5 );
		var phi = 2 * Math.PI * ( azi - 0.5 );

		var sX = sunLightparam.distance * Math.cos( phi );
		var sY = sunLightparam.distance * Math.sin( phi ) * Math.sin( theta );
		var sZ = sunLightparam.distance * Math.sin( phi ) * Math.cos( theta );
		var sP = { r: sX, g: sY, b: sZ };
		//console.log('sP:', sP);
		
		ion.animateParamToSMP6('position', sunLight.position, { x: sX, y: sY, z: sZ }, 800, easeInOutSine);
		setTimeout(stepOne, 900);

		function stepOne() {
			ion.scene.getObjectByName('sky').material.uniforms[ 'sunPosition' ].value = sP;
			ion.scene.getObjectByName('cubeCamera').update( ion.renderer, ion.scene.getObjectByName('sky') );
			//ion.requestRender();
			//console.log('sunLight.position:', ion.scene.getObjectByName('sunLight').position);
		}
		
		if (inc > 0.5 && inc < 1.01  ) {
				var ambInt = 0.1 + (inc - 0.2)
			} else {
				var ambInt = 0.1;
			}
		ion.animateParamToSMP('intensity', ion.scene.getObjectByName('light_ambient'), {intensity: ambInt}, 800, easeInOutSine);
		
		if (inc > 0.46 && inc < 0.8  ) {
			var rgbF = (-0.75+(inc)) * -2.2;
			var lMult = 0.85;
			var sRv = (0.95 * lMult) - rgbF;
			var sGv = (0.88 * lMult) - rgbF;
			var sBv = (0.72 * lMult) - rgbF;
			var envMult = 1.5+(rgbF *5.5);
			
			
			if (sRv >= 0 && sRv < 1) {
				var sR = sRv;
			} else {
				var sR = 0;
			}			
			if (sGv >= 0 && sGv < 1) {
				var sG = sGv;
			} else {
				var sG = 0;
			}			
			if (sBv >= 0 && sBv < 1) {
				var sB = sBv;
			} else {
				var sB = 0;
			}
			
			ion.animateParamToSMP('color', ion.scene.getObjectByName('sunLight').color, { r: sR, g: sG, b: sB }, 800, easeInOutSine);
			// console.log('case1:', rgbF)
			// console.log('envMult:', envMult);
			ion.sc.materials.m_04.envMapIntensity = ion.sc.materials.m_01.envMapIntensity = envMult;
			
			setTimeout(stepOne, 1600);

			function stepOne() {
				ion.animateParamToSMP('color', ion.scene.getObjectByName('light_cam').color, { r: sR, g: sG, b: sB }, 800, easeInOutSine);
			}
		} else {
			ion.animateParamToSMP('color', ion.scene.getObjectByName('sunLight').color, { r: 0.01, g: 0.01, b: 0.02 }, 1600, easeInOutSine);
			ion.sc.materials.m_04.envMapIntensity = ion.sc.materials.m_01.envMapIntensity = 7;
			//console.log('case3')
		
			setTimeout(stepOne, 1600);

			function stepOne() {
				ion.animateParamToSMP('color', ion.scene.getObjectByName('light_cam').color, { r: 0.004, g: 0.004, b: 0.02 }, 800, easeInOutSine);
			}
		}
		
	},
	
	initAudio : function() {
	
		const listener = new THREE.AudioListener();
		listener.name = 'audioMain';
		this.camera.add( listener );

		// create a global audio source
		const sound = new THREE.Audio( listener );

		// load a sound and set it as the Audio object's buffer
		const audioLoader = new THREE.AudioLoader();
		audioLoader.load( this.options.audio_track, function( buffer ) {
			sound.setBuffer( buffer );
			sound.setLoop( true );
			sound.setVolume( 0.9 );
			sound.play();
		});
		this.camera.getObjectByName("audioMain").gain.context.sampleRate=44100;
	},

    initWater : function ( sc ) {

       // Water

		var waterGeometry = new THREE.PlaneBufferGeometry( 12000, 12000 );

		var water = new THREE.Water(
			waterGeometry,
			{
				textureWidth: 1024,
				textureHeight: 1024,
				waterNormals: new THREE.TextureLoader().load( '_yh1/_tex/waternormals.jpg', function ( texture ) {

					texture.wrapS = texture.wrapT = THREE.RepeatWrapping;

				} ),
				alpha: 0.7,
				//sunDirection: (0.3, 0.4),
				sunColor: 0x888888,
				waterColor: 0x484C54,
				fogColor: 0xfeeacc,
				distortionScale: 3.7
			}
		);

		water.rotation.x = -Math.PI / 2;
		water.position.y = -24;
		water.name = 'water';
		
		//water.material.uniforms[ 'time' ].value += 1.0 / 60.0;

		ion.scene.getObjectByName('main_group').add( water );
		ion.requestRender();
    },

    separateInterfaceGroup : function ( contents ) {

        var index = 0;
        var scope = this;

        while ( true ) {

            var interfaceObject = contents.scene.getObjectByName( 'interface_group' + (index++) );
            if ( interfaceObject ) {

                var interfaceFrame = new THREE.InterfaceFrame( this, interfaceObject.userData );
                contents.scene.remove( interfaceObject );
                interfaceFrame.scene.add( interfaceObject );
                scope.interfaces.push( interfaceFrame );

            } else
                break;
        }


    },

    initSecondaryScene : function ( contents ) {

        this.secondary_sc = contents;
        this.secondary_group = contents.scene.getObjectByName( 'main_group' );

        for ( var i = 0; i < contents.scene.children.length; i++ ) {

            this.scene.add( contents.scene.children[ i ] );

        }
        this.secondary_group.rotation.set( this.main_group.rotation.x, this.main_group.rotation.y, this.main_group.rotation.z );
        this.render();
    },

    initContainer : function () {
        if ( this.options.container && this.options.container instanceof HTMLElement ) {
            this.container = this.options.container;
        } else {
            this.container = $c( 'div' );
            document.body.appendChild( this.container );
        }

        if( this.options.showStatus){
            this.status_info = $c( 'div' );
            this.status_info.id = 'status-info';
            this.container.appendChild( this.status_info );
            this.showStatus( 'Loading...' );
        }

        this.container.style.animationDuration = '1s';

        this.updateAspect();


    },

    addCamerasButtons : function ( parent ) {
        if ( !parent ) return;

        var _this = this;

        for ( var cam_name in this.sc.cameras ) {
            var link = $c( 'a' );
            link.setAttribute( 'data-target', cam_name );
            link.innerHTML = cam_name;

            link.addEventListener( 'click', function () {
                var cam_name = this.getAttribute( 'data-target' );
                _this.switchCamera( cam_name );
                return false;
            } );

            parent.appendChild( link );
        }
    },

    initCamera : function () {
        var _this = this;

        var op = this.options.camera;
        var pos = op.position;


        // select camera
        if ( this.sc.currentCamera ) {
            this.camera = this.sc.currentCamera;
        } else if ( any( this.sc.cameras ) ) {
            this.camera = first( this.sc.cameras );
        } else {
            this.log( 'create camera' );
            this.camera = new THREE.PerspectiveCamera( 35, this.aspect.width / this.aspect.height, 0.1, 1500 );
            this.camera.position.set( pos.x, pos.y, pos.z );
        }

        //this.camera.lookAt( this.scene.position );

        // preserve before switch
        this.camera.userData.default_position = {
            x : this.camera.position.x,
            y : this.camera.position.y,
            z : this.camera.position.z
        };
        this.camera.userData.default_fov = this.camera.fov;

        this.camera.userData.default_target = this.camera.target;

        this.log( 'camera', this.camera );

        this.cam_group = new THREE.Object3D();
        this.cam_group.add( this.camera );
        this.scene.add( this.cam_group );

        window.addEventListener( 'resize', function () {
            _this.updateCameraAspect();
        } );

        if ( typeof IonMouseControl !== 'undefined' ) {
            this.controls = new IonMouseControl( this, { debug : this.options.debug } );
        } else if ( THREE.OrbitControls ) {
            this.orbit_controls = new THREE.OrbitControls( this.camera, this.container );
            this.orbit_controls.enableDamping = this.camera.userData.parameters.enableDamping;
            this.orbit_controls.dampingFactor = this.camera.userData.parameters.dampingFactor;
			this.orbit_controls.minDistance = this.camera.userData.parameters.minDistance;
			this.orbit_controls.maxDistance = this.camera.userData.parameters.maxDistance;
			this.orbit_controls.minPolarAngle = this.camera.userData.parameters.minPolarAngle;
			this.orbit_controls.maxPolarAngle = this.camera.userData.parameters.maxPolarAngle;
			this.orbit_controls.panSpeed = this.camera.userData.parameters.panSpeed;
			this.orbit_controls.rotateSpeed = this.camera.userData.parameters.rotateSpeed;
			this.orbit_controls.zoomSpeed = this.camera.userData.parameters.zoomSpeed;
			this.orbit_controls.enableRotate = this.camera.userData.parameters.enableRotate;
			this.orbit_controls.enableZoom = this.camera.userData.parameters.enableZoom;
			if (this.camera.userData.parameters.enablePan == true) {
				this.orbit_controls.enablePan = true;
			} else {
				this.orbit_controls.enablePan = false;
			}
			
			if (this.camera.userData.parameters.screenSpacePanning !== undefined) {
				this.orbit_controls.screenSpacePanning = this.camera.userData.parameters.screenSpacePanning;
			} else {
				this.orbit_controls.screenSpacePanning = true;
			}

            this.orbit_controls.addEventListener( 'change', function () {
                _this.log( 'cont change' );

                _this.requestRender();
            } );

            this.cameraAnimationHelper = new THREE.CameraAnimationHelper( this );

        }

        if ( this.options.camera_info ) {
            this.showCamInfo();
        }
    },

    updateAspect : function () {
        this.aspect = {
            width : this.container.clientWidth,
            height : this.container.clientHeight
        }
    },

    updateCameraAspect : function () {
        if ( this.renderer == null ) return;

        this.updateAspect();
        this.camera.aspect = this.aspect.width / this.aspect.height;
        this.camera.updateProjectionMatrix();
        this.renderer.setSize( this.aspect.width, this.aspect.height );
        this.requestRender()
    },

    initRenderer : function () {
        var _this = this;

        // renderer
        this.renderer = new THREE.WebGLRenderer( { antialias : true, alpha : true, powerPreference: "high-performance", precision: "highp" } );
        this.renderer.gammaFactor = this.options.g_f;
        this.renderer.gammaInput = this.options.g_in;
        this.renderer.gammaOutput = this.options.g_out;
        this.renderer.physicallyBasedShading = this.options.phys_shading;
        this.renderer.toneMappingExposure = this.options.exposure;
		//this.renderer.setClearColor( '#ffffff' );

        this.renderer.setSize( this.aspect.width, this.aspect.height );

        this.container.appendChild( this.renderer.domElement );

        this.updateCameraAspect();

        // stats
        if ( this.options.stats ) {
            this.stats = new Stats();
            this.container.appendChild( this.stats.domElement );

            // render info
            var ri_cont = $c( 'div' );
            ri_cont.id = 'render-info';
            this.container.appendChild( ri_cont );

            this.render_info = $c( 'pre' );
            ri_cont.appendChild( this.render_info );

            var ri_refresh = $c( 'a' );
            ri_refresh.innerHTML = 'Refresh';
            ri_cont.appendChild( ri_refresh );
            ri_refresh.addEventListener( 'click', function () {
                _this.updateRenderInfo();
            } );

            this.updateRenderInfo();
        }

        if ( this.explorer ) {
            this.explorer.init();
        }
    },

    updateRenderInfo : function () {
        var i = this.renderer.info;

        this.render_info.innerHTML =
            '<ul>' +
            '<li>memory' +
            '<ul>' +
            '<li>geometries ' + i.memory.geometries + '</li>' +
            '<li>programs ' + i.memory.programs + '</li>' +
            '<li>textures ' + i.memory.textures + '</li>' +
            '</ul>' +
            '</li>' +
            '<li>render' +
            '<ul>' +
            '<li>calls ' + i.render.calls + '</li>' +
            '<li>faces ' + i.render.faces + '</li>' +
            '<li>points ' + i.render.points + '</li>' +
            '<li>vertices ' + i.render.vertices + '</li>' +
            '</ul>' +
            '</li>' +
            '<li>ts ' + (Date.now()) + '</li>' +
            '</ul>';


    },

    showStatus : function ( text ) {
        if( !this.options.showStatus ) return;
        var el = this.status_info;

        el.innerHTML = text;
        el.style.display = 'block';
    },

    hideStatus : function () {
        if(this.status_info)
            this.status_info.style.display = 'none';
    },

    start : function () {
        var scope = this;
        var start_time = (+new Date);


        this.showStatus( 'Rendering...' );
        this.animate();
        this.animate_simple();
        this.hideStatus();

        this.log( 'started', (+new Date) - start_time );

        this.spinRound();
/*
        if ( this.options.secondary_scene != null )
            setTimeout( function () {
                scope.loadSecondaryScene();
            }, 1100 );
*/
        this.animationHelper = new THREE.AnimationsHelper(
            this,
            function (name, arguments, duration, delay, easing, callback, endCallback) {
			
                scope.animateParamTo(name, arguments, duration, delay, easing, callback, endCallback);
				//scope.animateParamToSMP(name, obj, end, duration, easing, callback); // very strange

            });
        this.requestRender();

    },

    requestRender : function () {
        this.needRender = true;
    },

    spinRound: function () {
    	if (this.options.spin) {
    		this.animateParamToSMP('rotate', this.main_group.rotation, { x: 0, y: this.options.rotate_left +(Math.PI * 2), z: 0 }, 900, easeInOutCubic);
    		this.log('spin');
            setTimeout(resetY, 1000);
			
			function resetY() {
			ion.sc.objects.main_group.rotation.y = ion.options.rotate_left;
				}
    	} else {}
    },

    applyAnimationToNewMaterial : function ( object ) {

        var dataClick = object.userData.click;

        if ( dataClick ) {

            for ( var key in dataClick ) {
                var args = dataClick[ key ].backAnimation;
                if ( args ) {

                    for ( var argKey in args ) {

                        if ( argKey.indexOf( 'material' ) > -1 ) {

                            setArgs( argKey, dataClick[ key ].clickOn ? args[ argKey ].obj : args[ argKey ].end );
                        }

                    }

                }

            }

        }
        function setArgs ( key, data ) {

            if ( data instanceof Object ) {

                var objectArgument = getDiffArgument( key, object )
                for ( var dataKey in data ) {
                    objectArgument[ dataKey ] = data[ dataKey ];
                }

            } else {
                setParam( key, data );
            }
        }


        function setParam ( key, value ) {

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
    },

    animateParamToSky: function (name, obj, end, duration, easing, callback) {
        // clone hash
        var start = extend({}, end),
            delta = extend({}, end);

        // fill values
        for (var param in end) {
            start[param] = obj[param];
            delta[param] = end[param] - obj[param];
        }

        this.animations[name] = {
            obj: obj,
            start: start,
            delta: delta,
            callback: callback,
            duration: duration,
            easing: easing || easeInOutCubic,
            started: (+new Date)
        };
		//this.scene.getObjectByName('cubeCamera').update( this.renderer, this.scene.getObjectByName('sky') );
        //console.log('anim', name, obj, end, duration);
    },

    animateParamToSMP: function (name, obj, end, duration, easing, callback) {
        // clone hash
        var start = extend({}, end),
            delta = extend({}, end);

        // fill values
        for (var param in end) {
            start[param] = obj[param];
            delta[param] = end[param] - obj[param];
        }

        this.animations[name] = {
            obj: obj,
            start: start,
            delta: delta,
            callback: callback,
            duration: duration,
            easing: easing || easeInOutCubic,
            started: (+new Date)
        };

        //console.log('anim', name, obj, end, duration);
    },

    animateParamToSMP2: function (name, obj, end, duration, easing, callback) {
        // clone hash
        var start = extend({}, end),
            delta = extend({}, end);

        // fill values
        for (var param in end) {
            start[param] = obj[param];
            delta[param] = end[param] - obj[param];
        }

        this.animations[name] = {
            obj: obj,
            start: start,
            delta: delta,
            callback: callback,
            duration: duration,
            easing: easing || easeInOutCubic,
            started: (+new Date)
        };

        //console.log('anim', name, obj, end, duration);
    },

    animateParamToSMP3: function (name, obj, end, duration, easing, callback) {
        // clone hash
        var start = extend({}, end),
            delta = extend({}, end);

        // fill values
        for (var param in end) {
            start[param] = obj[param];
            delta[param] = end[param] - obj[param];
        }

        this.animations[name] = {
            obj: obj,
            start: start,
            delta: delta,
            callback: callback,
            duration: duration,
            easing: easing || easeInOutCubic,
            started: (+new Date)
        };

        // console.log('anim', name, obj, end, duration);
    },


    animateParamToSMP4: function (name, obj, end, duration, easing, callback) {
        // clone hash
        var start = extend({}, end),
            delta = extend({}, end);

        // fill values
        for (var param in end) {
            start[param] = obj[param];
            delta[param] = end[param] - obj[param];
        }

        this.animations[name] = {
            obj: obj,
            start: start,
            delta: delta,
            callback: callback,
            duration: duration,
            easing: easing || easeInOutCubic,
            started: (+new Date)
        };

        //this.log('anim', name, obj, end, duration);
    },


    animateParamToSMP5: function (name, obj, end, duration, easing, callback) {
        // clone hash
        var start = extend({}, end),
            delta = extend({}, end);

        // fill values
        for (var param in end) {
            start[param] = obj[param];
            delta[param] = end[param] - obj[param];
        }

        this.animations[name] = {
            obj: obj,
            start: start,
            delta: delta,
            callback: callback,
            duration: duration,
            easing: easing || easeInOutCubic,
            started: (+new Date)
        };

        //console.log('anim', name, obj, end, duration);
    },


    animateParamToSMP6: function (name, obj, end, duration, easing, callback) {
        // clone hash
        var start = extend({}, end),
            delta = extend({}, end);

        // fill values
        for (var param in end) {
            start[param] = obj[param];
            delta[param] = end[param] - obj[param];
        }

        this.animations[name] = {
            obj: obj,
            start: start,
            delta: delta,
            callback: callback,
            duration: duration,
            easing: easing || easeInOutCubic,
            started: (+new Date)
        };

        //console.log('anim', name, obj, end, duration);
    },

    animateParamTo: function (name, arguments, duration, delay, easing, callback, callbackEnd) {

        this.animations[name] = {
            args: {},
            callback: callback,
            callbackEnd: callbackEnd,
            duration: duration,
            easing: easing || easeOutCubic,
            started: (+new Date) + delay
        };

        for (var argument in arguments) {

            if (arguments[argument].obj instanceof Object) {
                var obj = {};

                var end = {};

                for (var key in arguments[argument].obj) {
                    if (['x', 'y', 'z', 'w', 'r', 'g', 'b'].indexOf(key) >= 0) {
                        obj[key] = arguments[argument].obj[key];
                        end[key] = arguments[argument].end[key];
                    }
                }

                var start = extend({}, end);
                var delta = extend({}, end);

                for (var param in end) {
                    start[param] = obj[param];
                    delta[param] = end[param] - obj[param];
                }
            } else {

                var obj = arguments[argument].obj;
                var end = arguments[argument].end;
                var start = obj;
                var delta = end - obj;
            }

            this.animations[name].args[argument] = {
                obj: obj,
                start: start,
                delta: delta
            };
        }
		//this.log(name, arguments, duration, delay, easing, callback, callbackEnd);
    },

    // {x: 0, y: 0}
    setRotation: function (rotate) {
        this.animateParamToSMP('rotate', this.cam_group.rotation, rotate, 1000, easeOutCubic);
    },

    setFov: function (fov) {
        this.animateParamToSMP('fov', this.camera, {
            fov: fov
        }, 8000, easeOutCubic);
    },

    switchCamera : function ( cam_name, duration, easingCam ) {
        var scope = this;
        var cameraCurrentView = this.sc.cameras[ cam_name ];

        if ( cameraCurrentView ) {

            var easing = easingCam || (cameraCurrentView.userData ? cameraCurrentView.userData.easing : undefined);

            if( easing == null )
                duration = 0;

            if ( this.camera.name == cam_name ) {
                var new_pos = this.camera.userData.default_position;
                var new_fov = this.camera.userData.default_fov;
                var target = this.camera.userData.default_target;

            } else {
                var new_cam = this.sc.cameras[ cam_name ];
                var new_pos = new_cam.position
                    ? { x : new_cam.position.x, y : new_cam.position.y, z : new_cam.position.z }
                    : this.options.camera.position;
                var new_fov = new_cam.fov || this.options.camera.fov;
                var target = new_cam.target || this.options.camera.target;
            }

            var arguments = {

                position : new_pos,
                target : target,
                fov : new_fov,
                easing : easing
            };

            this.moveCamera( 'camera_fov', arguments, duration, undefined, easing, function () {
                scope.cameraAnimationHelper.applyOptions( cameraCurrentView );
            } )


        }
    },

    switchTarget: function (cam_name, duration, easingCam) {
        var scope = this;
        var cameraCurrentView = this.sc.cameras[cam_name];

        if (cameraCurrentView) {

            var easing = easingCam || (cameraCurrentView.userData ? cameraCurrentView.userData.easing : undefined);

            if (easing == null)
                duration = 0;

            if (this.camera.name == cam_name) {
                var new_fov = this.camera.userData.default_fov;
                var target = this.camera.userData.default_target;

            } else {
                var new_cam = this.sc.cameras[cam_name];
                var new_fov = new_cam.fov || this.options.camera.fov;
                var target = new_cam.target || this.options.camera.target;
            }

            var arguments = {

                target: target,
                fov: new_fov,
                easing: easing
            };

            this.moveCamera('camera_fov', arguments, duration, undefined, easing, function () {
                scope.cameraAnimationHelper.applyOptions(cameraCurrentView);
            })

        }
    },

    moveCamera: function (animationName, arguments, duration, delay, easing, onAnimate) {

        var scope = this;
        animate(arguments, duration == 0 ? 0 : duration || 1800, delay || 0);

        function animate(args, duration, delay) {

            scope.orbit_controls.focus(
                args,
                function (name, arguments, callback, callbackEnd) {

                    if (easing == null) {
                        scope.container.style.animationName = "changeCamera";
                        setTimeout(function () {
                            scope.animateParamTo(animationName, arguments, duration, delay, getCurve(easing), callback, callbackEnd);
                            scope.container.style.animationName = "";
                        }, 500);
                    } else {
                        scope.animateParamTo(animationName, arguments, duration, delay, getCurve(easing), callback, callbackEnd);
                    }

                },
                onAnimate);

        }
    },

    animateCamera: function (animationName, args, duration, delay, onAnimate) {

        var scope = this;

        this.orbit_controls.focus2(
            args,
            function (name, arguments, callback, callbackEnd) {

                scope.animateParamTo(animationName, arguments, duration, delay, null, callback, callbackEnd);
            },
            onAnimate);

    },

    disposeWebGL : function () {

        var scope = this;

        this.clearScene( this.sc.scene, this.camera );

        this.animationHelper.dispose();

        this.interfaces.map( function ( object ) {

            scope.clearScene( object.scene, object.camera, object.renderer );

        } );

        for ( var key in this.sc.cameras ) {
            var camera = this.sc.cameras[ key ];
            scope.clearScene( camera );
            if ( camera.parent ) {
                camera.parent.remove( camera );
            } else {
                this.sc.cameras[ key ] = null;
            }
        }

        for ( var key in this.sc.empties ) {
            var object = this.sc.empties[ key ];
            scope.clearScene( object );
            if ( object.parent ) {
                object.parent.remove( object );
            } else {
                this.sc.empties[ key ] = null;
            }
        }

        for ( var key in this.sc.geometries ) {
            var geometry = this.sc.geometries[ key ];
            geometry.dispose();
            this.sc.geometries[ key ] = null;
        }

        for ( var key in this.sc.lights ) {
            var light = this.sc.lights[ key ];
            if ( light.parent ) {
                light.parent.remove( light );
            } else {
                this.sc.lights[ key ] = null;
            }
        }

        for ( var key in this.sc.materials ) {
            var material = this.sc.materials[ key ];
            if ( material instanceof THREE.MultiMaterial ) {

                for ( var i = 0; i < material.materials.length; i++ ) {
                    material.materials[ i ].dispose();
                }

            } else {
                material.dispose();
            }
            this.sc.materials[ key ] = null
        }

        for ( var key in this.sc.objects ) {
            var object = this.sc.objects[ key ];
            scope.clearScene( object );
            if ( object.parent ) {
                object.parent.remove( object );
            } else {
                this.sc.objects[ key ] = null;
            }
        }

        for ( var key in this.sc.textures ) {
            var texture = this.sc.textures[ key ];
            texture.dispose();
            this.sc.textures[ key ] = null;
        }

        this.orbit_controls.dispose();
        this.orbit_controls = null;

        this.sc.scene = null;

        this.renderer.dispose();
        this.requestRender();

        setTimeout(function (){
            scope.renderer = null;
        },0)

    },

    clearScene : function ( scene, camera, renderer ) {

        var objects = scene.children;

        while ( objects.length > 0 ) {

            removeObject( objects[ 0 ] );

        }

        function removeObject ( object ) {


            object.traverse( function ( child ) {

                if ( child.dispose ) child.dispose();
                if ( child.material ) {
                    if ( child.material instanceof THREE.MultiMaterial ) {

                        for ( var i = 0; i < child.material.materials.length; i++ ) {
                            child.material.materials[ i ].dispose();
                        }

                    } else {
                        child.material.dispose();
                    }

                }
                if ( child.geometry ) child.geometry.dispose();

            } );

            object.parent.remove( object );
            if ( object.dispose ) object.dispose();
            if ( object.material ){
                if ( object.material instanceof THREE.MultiMaterial ) {

                    for ( var i = 0; i < object.material.materials.length; i++ ) {
                        object.material.materials[ i ].dispose();
                    }

                } else {
                    object.material.dispose();
                }
            }
            if ( object.geometry ) object.geometry.dispose();
        }

        if ( scene ) scene = null;
        if ( camera ) camera = null;
        if ( renderer ) {
            renderer.dispose();
            renderer = null;
        }

    },


    animate_simple: function(t) {
        var _this = this;
        requestAnimationFrame(function(t){ _this.animate_simple(t) }, this.renderer.domElement);
							  
        this.processAnimations();

    },
	
    animate: function (t) {

        if (this.renderer == null) {
            cancelAnimationFrame(this.requestAnimate);
            return;
        }

        var _this = this;
        this.requestAnimate = requestAnimationFrame(function (t) {
            _this.animate(t)
        }, this.renderer.domElement);

        this.processAnimations2();

        if (this.orbit_controls) {
            this.orbit_controls.update();
        }

        if (this.needRender || this.options.always_render) {
            this.render(t);
            this.needRender = false;
        }

        if (this.options.stats)
            this.stats.update();

    },


    processAnimations: function () {
        for (var name in this.animations) {
            var anim = this.animations[name],
                obj = anim.obj,
                timer = (+new Date) - anim.started;

            for (var param in anim.start) {
                var start_val = anim.start[param],
                    delta = anim.delta[param],
                    new_val = anim.easing(timer, start_val, delta, anim.duration);

                obj[param] = new_val;

                if (obj == this.camera) {
                    this.camera.updateProjectionMatrix();
                }
            }

            if (name == 'cam switch') {
                this.camera.lookAt(this.scene.position);
            }

            if (timer > anim.duration) {
                //                    this.log('anim stop', name, new_val);
                if (anim.callback) {
                    anim.callback.call(this, obj);
                }

                delete this.animations[name];
            }

            this.requestRender();
        }
    },

    processAnimations2: function () {

        var event = false;

        for (var name in this.animations) {

            var anim = this.animations[name],
                arguments = anim.args,
                timer = (+new Date) - anim.started;

            if (anim.started > (+new Date))
                continue;

            if (anim.easing && anim.easing != null)
                for (var argument in arguments) {

                    var start = arguments[argument].start;
                    var delta = arguments[argument].delta;

                    if (arguments[argument].obj instanceof Object) {

                        for (var param in start) {
                            var startValue = start[param],
                                deltaValue = delta[param],
                                newValue = anim.easing(timer, startValue, deltaValue, anim.duration);

                            arguments[argument].obj[param] = newValue;
                        }

                    } else {

                        var newValue = anim.easing(timer, start, delta, anim.duration);
                        arguments[argument].obj = newValue;
                    }

                }

            if (anim.callback) {

                anim.callback.call(this, arguments);
            }

            if (timer > anim.duration) {

                if (anim.callbackEnd) {
                    anim.callbackEnd.call(this, arguments);
                }
                delete this.animations[name];

            }

            event = true;
        }

        if (Object.keys(this.autorotations).length > 0) {

            var delta = this.clock.getDelta();

            for (var key in this.autorotations) {

                var object = this.autorotations[key];
                if (object.userData.autorotation.enabled) {

                    for (var axis in object.userData.autorotation.speed) {

                        object.rotation[axis] += object.userData.autorotation.speed[axis] * delta;

                    }
                    event = true;
                }
            }

        }
        if (event)
            this.requestRender();
    },


    render : function ( t ) {
		
		ion.scene.getObjectByName('sky').material.uniforms[ 'sunPosition' ].value = ion.scene.getObjectByName('sunLight').position;
		ion.scene.getObjectByName('cubeCamera').update( ion.renderer, ion.scene.getObjectByName('sky') );

        this.renderer.clear();
        this.renderer.render( this.scene, this.camera );
		
		var time = performance.now() * 0.001;
		if (ion.data.param.live_data.current.wind_kph >= 10) {
			var speed = ion.data.param.live_data.current.wind_kph/8;
		} else  {
			var speed = 1;
		}
		this.sc.objects.main_group.getObjectByName('water').material.uniforms[ 'time' ].value += (speed) / 100;

        for ( var i = 0, j = this.interfaces.length; i < j; i++ ) {
            this.interfaces[ i ].renderer.clear();
            this.interfaces[ i ].renderer.render( this.interfaces[ i ].scene, this.interfaces[ i ].camera );
        }
		
        if ( this.options.camera_info ) this.showCamInfo();
        if ( this.options.weather_info ) this.showLiveWeather();
    },
	
	fetchWeather : function () {
	
		var today = new Date();
		var dd = String(today.getDate()).padStart(2, '0');
		var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
		var yyyy = today.getFullYear();

		var todayAstro = 'http://api.weatherapi.com/v1/astronomy.json?key=86e2cee98e40449a969174824200812&q=47.7086, -52.7144&dt=' + yyyy + '-' + mm + '-' + dd;
		console.log(todayAstro);
	
	
		fetch('http://api.weatherapi.com/v1/current.json?key=86e2cee98e40449a969174824200812&q=47.7086, -52.7144').then(res => res.json()).then(data => ion.data.param.live_data = data);	
		// fetch(todayAstro).then(res => res.json()).then(data => ion.data.param.live_astro = data);
		
		setTimeout(stepOne, 3000);
		setTimeout(stepTwo, 300000);

		function stepOne() {
			ion.updateWeather();
			ion.updateSun();
			
			// if ( ion.options.weather_info !== false && undefined ) {
				// ion.showLiveWeather();
			// }
			
		}
		
		function stepTwo() {
			ion.fetchWeather();
		}
		
	},
	
	updateWeather : function() {
		var wind = ( ion.data.param.live_data.current.wind_degree / 57.325 );
		var windSpeed = ( (ion.data.param.live_data.current.wind_kph / 57.325)/2 );
		var swivMult = ion.data.param.swiv_factor;
		if (ion.data.param.live_data.current.wind_degree < 45 || ion.data.param.live_data.current.wind_degree > 315 && ion.data.param.live_data.current.wind_kph <= 80 ) {
			var speedFov = ( 35 + ion.data.param.live_data.current.wind_kph / 1.66 );
			ion.setFov(speedFov);
		} else if (ion.data.param.live_data.current.wind_degree < 45 || ion.data.param.live_data.current.wind_degree > 315 && ion.data.param.live_data.current.wind_kph > 80 ) {
			ion.setFov(speedFov);
		} else {
			ion.setFov(45);
		}
		var stepDuration = 300000/((ion.data.param.live_data.current.wind_kph/100)+1);
		var randomSeed = 3;
		
		//ion.sc.objects.house.rotation.z = wind;
		if (ion.data.param.live_data.current.wind_degree !== ion.data_stored.param.live_data.current.wind_degree) {
			ion.animateParamToSMP3('rotate_house', ion.sc.objects.h1_group.rotation, { x: 0, y: 0, z: wind }, 300000, easeInOutCubic);
			ion.data_stored.param.live_data.current.wind_degree = ion.data.param.live_data.current.wind_degree;
			console.log('rotate house to win direction');
		} else {
		
			setTimeout(stepOne, 0);
			setTimeout(stepTwo, stepDuration);
			
			function stepOne() {
			var valRandom1 = round ((((Math.random() * (randomSeed - (-randomSeed)) + randomSeed)*0.1)+1*swivMult), 100);
			var Z = round(wind + (windSpeed*valRandom1), 100);
			console.log('step one: ', valRandom1, "angle: ", Z, round((Z * 57.325), 1), '°' );
			ion.animateParamToSMP3('rotate_house', ion.sc.objects.h1_group.rotation, { x: 0, y: 0, z: Z }, stepDuration, easeInOutSine);
			}
			
			function stepTwo() {
			var valRandom1 = round ((((Math.random() * (randomSeed - (-randomSeed)) + randomSeed)*0.1)+1*swivMult), 100);
			var Z = round(wind - (windSpeed*valRandom1), 100);
			console.log('step two: ', valRandom1, "angle: ", Z, round((Z * 57.325), 1), '°' );
			ion.animateParamToSMP3('rotate_house', ion.sc.objects.h1_group.rotation, { x: 0, y: 0, z: Z }, stepDuration, easeInOutSine);
			}
		}
		
		
	},
	
	animateHouse : function() {
	
		var wind = ion.data.param.live_data.current.wind_kph;
		
		if (wind <= 9) {
			var stepDuration = 5000;
			//this.log('wind 0');
		} else if (wind <= 29) {
			var stepDuration = 4500;
			//this.log('wind 10 29');
		} else if (wind <= 39 ) {
			var stepDuration = 4000;
			//this.log('wind 30 39');
		} else if (wind <= 49 ) {
			var stepDuration = 3500;
			//this.log('wind 40 49');
		} else if (wind <= 59 ) {
			var stepDuration = 3000;
			//this.log('wind 50 59');
		} else if (wind <= 79 ) {
			var stepDuration = 3000;
			//this.log('wind 60 79');
		} else if (wind <= 80 ) {
			var stepDuration = 2800;
			//this.log('wind 80');
		} else {
			//this.log('wind 90');
		
		}
		
		var randomSeed = 3;
		
		var posMult = ion.data.param.wind_factor +(wind/20);
		var rotMult = ion.data.param.wind_factor +ion.data.param.sway_factor +(wind/20);
		var swivMult = ion.data.param.swiv_factor +(wind/20);
		
		setTimeout(stepOne, 0);
		setTimeout(stepTwo, stepDuration);
		setTimeout(stepThree, stepDuration*2);
		setTimeout(stepFour, stepDuration*3);

		function stepOne() {
			var valRandom1 = round (((Math.random() * (randomSeed - (-randomSeed)) + randomSeed)*0.1), 100)+0.5;
			var valRandom2 = round (((Math.random() * (randomSeed - (-randomSeed)) + randomSeed)*0.1), 100)+0.5;
			var valRandom3 = round (((Math.random() * (randomSeed - (-randomSeed)) + randomSeed)*0.1), 100)+0.5;
			// console.log('random:', valRandom)
			ion.animateParamToSMP4('rocking_position', ion.sc.objects.h2_group.position, { /*x: 0.0,*/ y: 0.1*posMult*valRandom1, z: 0.1*posMult*valRandom2 }, stepDuration, easeInOutSine);
			ion.animateParamToSMP2('rocking_rotation', ion.sc.objects.h3_group.rotation, { x: 0.0005*rotMult*valRandom3, y: 0.002*rotMult*valRandom2/*, z: round(-0.004*swivMult*valRandom1, 10000)*/  }, stepDuration, easeInOutSine);
			//console.log('new sequence', swivMult, round(-0.004*swivMult*valRandom1, 10000));
		}
		
		function stepTwo() {
			var valRandom1 = round (((Math.random() * (randomSeed - (-randomSeed)) + randomSeed)*0.1), 100)+0.5;
			var valRandom2 = round (((Math.random() * (randomSeed - (-randomSeed)) + randomSeed)*0.1), 100)+0.5;
			var valRandom3 = round (((Math.random() * (randomSeed - (-randomSeed)) + randomSeed)*0.1), 100)+0.5;
			ion.animateParamToSMP4('rocking_position', ion.sc.objects.h2_group.position, { /*x: -0.0*posMult, */ y: 0, z: -0.2*posMult*valRandom1 }, stepDuration, easeInOutSine);
		ion.animateParamToSMP2('rocking_rotation', ion.sc.objects.h3_group.rotation, { x: -0.0005*rotMult*valRandom2, y: -0.002*rotMult*valRandom1/*, z: round(0.005*swivMult*valRandom3, 10000)*/ }, stepDuration, easeInOutSine);
			//console.log('new sequence', swivMult, round(0.005*swivMult*valRandom3, 10000));
		}
		
		function stepThree() {
			var valRandom1 = round (((Math.random() * (randomSeed - (-randomSeed)) + randomSeed)*0.1), 100)+0.5;
			var valRandom2 = round (((Math.random() * (randomSeed - (-randomSeed)) + randomSeed)*0.1), 100)+0.5;
			var valRandom3 = round (((Math.random() * (randomSeed - (-randomSeed)) + randomSeed)*0.1), 100)+0.5;
			ion.animateParamToSMP4('rocking_position', ion.sc.objects.h2_group.position, { /*x: 0.0,*/ y: 0.1*posMult*valRandom1, z: 0.1*posMult*valRandom2 }, stepDuration, easeInOutSine);
		ion.animateParamToSMP2('rocking_rotation', ion.sc.objects.h3_group.rotation, { x: 0.0005*rotMult*valRandom3, y: 0.002*rotMult*valRandom2/*, z: round(-0.004*swivMult*valRandom2, 10000)*/ }, stepDuration, easeInOutSine);
			//console.log('new sequence', swivMult, round(-0.004*swivMult*valRandom2, 10000));
			
		}
		
		function stepFour() {
			var valRandom1 = round (((Math.random() * (randomSeed - (-randomSeed)) + randomSeed)*0.1), 100)+0.5;
			var valRandom2 = round (((Math.random() * (randomSeed - (-randomSeed)) + randomSeed)*0.1), 100)+0.5;
			var valRandom3 = round (((Math.random() * (randomSeed - (-randomSeed)) + randomSeed)*0.1), 100)+0.5;
			ion.animateParamToSMP4('rocking_position', ion.sc.objects.h2_group.position, { /*x: -0.0*posMult, */ y: 0, z: -0.2*posMult*valRandom1 }, stepDuration, easeInOutSine);
		ion.animateParamToSMP2('rocking_rotation', ion.sc.objects.h3_group.rotation, { x: -0.0005*rotMult*valRandom2, y: -0.002*rotMult*valRandom1/*, z: round(0.005*swivMult*valRandom3, 10000)*/ }, stepDuration, easeInOutSine);
			
			if (ion.data.param.animation !== false) {
				setTimeout(ion.animateHouse, stepDuration);
				//console.log('new sequence', swivMult, round(0.005*swivMult*valRandom3, 10000));
			} else {}
		}
		
		/*
		
		{"position" : {"x": 0, "y": 1, "z": 0.5}, "rotation" : {"x": 0, "y": 0.01, "z": 0.02}},
		{"position" : {"x": 1, "y": 0, "z": -1}, "rotation" : {"x": 0.01, "y": 0.0, "z": -0.03}},
		{"position" : {"x": 0, "y": 1, "z": 0.5}, "rotation" : {"x": 0, "y": 0.02, "z": 0.01}},
		{"position" : {"x": 1, "y": 0, "z": -1}, "rotation" : {"x": 0.01, "y": -0.01, "z": -0.02}}
		
		*/
		
	},
	
	updateMaterial : function() {
		var opt = this.options;
		var url = "";
		// var url = opt.scene_assets_url + opt.woo_tex_url;
        var o = this.sc.objects;
		var mMat = this.data.param.mMat;
		var tex_loader = new THREE.TextureLoader();
		var pc = ion.data.param;
		var pcm = ion.data.param.materials;
		var st = ion.data_stored.param;
		var stm = ion.data_stored.param.materials;

		
		if (matNoo = 'mat_01', pcm[matNoo] != undefined || null)   {
			
			let matNo = matNoo;
			let matAr = matNo.substring(4,6);
			let pcMat = pcm[matNo];
			let stMat = stm[matNo];
			let arMat = ion.sc.materials.mm_procedural[matAr-1];
			let albedoTex = pcMat.map;
			let roughnessTex = pcMat.roughnessMap;
			let bumpTex = pcMat.bumpMap;
			
			if (albedoTex != undefined || null) {
				if (pcm[matNo].map != stm[matNo].map) {
					tex_loader.load(albedoTex,
						function (texture) {
						if (bumpTex == 'albedo' && roughnessTex == 'albedo') {
							arMat.map = arMat.bumpMap = arMat.roughnessMap = (texture);
							console.log('albedo' + matNo + 'for all');
						} else if (bumpTex == 'albedo' && roughnessTex !== 'albedo') {
							arMat.map = arMat.bumpMap = (texture);
							console.log('albedo' + matNo + '+ bump');
						} else if (bumpTex !== 'albedo' && roughnessTex == 'albedo') {
							arMat.map = arMat.roughnessMap = (texture);
							console.log('albedo' + matNo + '+ roughnessMap');
						} else {
							arMat.map = (texture);
							console.log('albedo' + matNo + 'just map');
						}
						texture.flipY = true;
						texture.anisotropy = 4;
						texture.repeat.set(pcMat.map_repeat, pcMat.map_repeat);
						texture.wrapS = texture.wrapT = 1000;
						stm[matNo].map = pcm[matNo].map;
						ion.requestRender();
					},
						function (xhr) {
						console.log((xhr.loaded / xhr.total * 100) + '% loaded');
					});
				} else {
					console.log('albedo_' + matNo + ' no change');
				}
			} else {
				console.log('albedo ' + matNo + ' null');
			}

			if (bumpTex != undefined || null) {
				if (pcm[matNo].bumpMap != stm[matNo].bumpMap) {
					if (bumpTex == 'albedo') {
						stm[matNo].bumpMap = pcm[matNo].bumpMap;
						console.log('bumpMap_' + matNo + ' //skipped');
					} else if (bumpTex.indexOf("jpg") !== -1 || bumpTex.indexOf("png") !== -1) {
						tex_loader.load(bumpTex,
							function (texture) {
							if (roughnessTex == 'bumpMap') {
								arMat.bumpMap = arMat.roughnessMap = (texture);
								console.log('bumpMap' + matNo + '+ roughnessMap');
							} else {
								arMat.bumpMap = (texture);
								console.log('bumpMap' + matNo + 'just bumpMap');
							}
							texture.flipY = true;
							texture.anisotropy = 4;
							texture.repeat.set(pcMat.bumpMap_repeat, pcMat.bumpMap_repeat);
							texture.wrapS = texture.wrapT = 1000;
							stm[matNo].bumpMap = pcm[matNo].bumpMap;
							ion.requestRender();
						},
							function (xhr) {
							console.log((xhr.loaded / xhr.total * 100) + '% loaded');
						})
					} else {
						console.log('bumpMap_' + matNo + '//strange case');
					}
				} else {
					stm[matNo].bumpMap = pcm[matNo].bumpMap;
					console.log('bumpMap_' + matNo + ' no change');
				}
			} else {
				console.log('bumpMap_' + matNo + '//null');
			}

			if (roughnessTex != undefined || null) {
				if (pcm[matNo].roughnessMap != stm[matNo].roughnessMap) {
					if (roughnessTex == 'albedo' || roughnessTex == 'bumpMap') {
						stm[matNo].roughnessMap = pcm[matNo].roughnessMap;
						console.log('roughnessMap_' + matNo + ' //skipped');
					} else if (roughnessTex.indexOf("jpg") !== -1 || roughnessTex.indexOf("png") !== -1) {
						tex_loader.load(roughnessTex,
							function (texture) {
							if (bumpTex == 'bumpMap') {
								arMat.bumpMap = arMat.roughnessMap = (texture);
								console.log('roughnessMap' + matNo + '+ bumpMap');
							} else {
								arMat.roughnessMap = (texture);
								console.log('roughnessMap' + matNo + 'just roughnessMap');
							}
							texture.flipY = true;
							texture.anisotropy = 4;
							texture.repeat.set(pcMat.bumpMap_repeat, pcMat.bumpMap_repeat);
							texture.wrapS = texture.wrapT = 1000;
							stm[matNo].roughnessMap = pcm[matNo].roughnessMap;
							ion.requestRender();
						},
							function (xhr) {
							console.log((xhr.loaded / xhr.total * 100) + '% loaded');
						})
					} else {
						console.log('roughnessMap_' + matNo + '//strange case');
					}
				} else {
					stm[matNo].roughnessMap = pcm[matNo].roughnessMap;
					console.log('roughnessMap_' + matNo + ' no change');
				}
			} else {
				console.log('roughnessMap_' + matNo + '//null');
			}

			
			if (pcMat.color != undefined || null ) {
				var h = pcMat.color.h;
				var s = pcMat.color.s;
				var l = pcMat.color.l;
				arMat.color.setHSL(h,s,l) ;
			} else {
				arMat.color.setHSL(0,0,0.5) ;
			}
			
			if ( bumpTex != null && pcMat.bumpScale != undefined || null) {
				arMat.bumpScale = pcMat.bumpScale;
			} else {
				arMat.bumpScale = 0;
			}
			
			if (pcMat.envMapIntensity != undefined || null ) {
				arMat.envMapIntensity = pcMat.envMapIntensity;
			} else {
				arMat.envMapIntensity = 1;
			}
			
			if (pcMat.metalness != undefined || null ) {
				arMat.metalness = pcMat.metalness;
			} else {
				arMat.metalness = 0;
			}
			
			if (pcMat.roughness != undefined || null ) {
				arMat.roughness = pcMat.roughness;
			} else {
				arMat.roughness = 0.5;
			}
			
			if (arMat.type == "MeshPhysicalMaterial" ) {
				if (pcMat.clearCoat != undefined || null ) {
					arMat.clearCoat = pcMat.clearCoat;
				} else {
					arMat.clearCoat = 0;
				}
				
				if (pcMat.clearCoatRoughness != undefined || null ) {
					arMat.clearCoatRoughness = pcMat.clearCoatRoughness;
				} else {
					arMat.clearCoatRoughness = 0;
				}
				
				if (pcMat.reflectivity != undefined || null ) {
					arMat.reflectivity = pcMat.reflectivity;
				} else {
					arMat.reflectivity = 0.5;
				}
			} else {
			}
			
			this.applyMaterials();
			this.requestRender();
			
		} else {
		}	

		
		if (matNoo = 'mat_02', pcm[matNoo] != undefined || null)   {
			
			let matNo = matNoo;
			let matAr = matNo.substring(4,6);
			let pcMat = pcm[matNo];
			let stMat = stm[matNo];
			let arMat = ion.sc.materials.mm_procedural[matAr-1];
			let albedoTex = pcMat.map;
			let roughnessTex = pcMat.roughnessMap;
			let bumpTex = pcMat.bumpMap;
			
			if (albedoTex != undefined || null) {
				if (pcm[matNo].map != stm[matNo].map) {
					tex_loader.load(albedoTex,
						function (texture) {
						if (bumpTex == 'albedo' && roughnessTex == 'albedo') {
							arMat.map = arMat.bumpMap = arMat.roughnessMap = (texture);
							console.log('albedo' + matNo + 'for all');
						} else if (bumpTex == 'albedo' && roughnessTex !== 'albedo') {
							arMat.map = arMat.bumpMap = (texture);
							console.log('albedo' + matNo + '+ bump');
						} else if (bumpTex !== 'albedo' && roughnessTex == 'albedo') {
							arMat.map = arMat.roughnessMap = (texture);
							console.log('albedo' + matNo + '+ roughnessMap');
						} else {
							arMat.map = (texture);
							console.log('albedo' + matNo + 'just map');
						}
						texture.flipY = true;
						texture.anisotropy = 4;
						texture.repeat.set(pcMat.map_repeat, pcMat.map_repeat);
						texture.wrapS = texture.wrapT = 1000;
						stm[matNo].map = pcm[matNo].map;
						ion.requestRender();
					},
						function (xhr) {
						console.log((xhr.loaded / xhr.total * 100) + '% loaded');
					});
				} else {
					console.log('albedo_' + matNo + ' no change');
				}
			} else {
				console.log('albedo ' + matNo + ' null');
			}

			if (bumpTex != undefined || null) {
				if (pcm[matNo].bumpMap != stm[matNo].bumpMap) {
					if (bumpTex == 'albedo') {
						stm[matNo].bumpMap = pcm[matNo].bumpMap;
						console.log('bumpMap_' + matNo + ' //skipped');
					} else if (bumpTex.indexOf("jpg") !== -1 || bumpTex.indexOf("png") !== -1) {
						tex_loader.load(bumpTex,
							function (texture) {
							if (roughnessTex == 'bumpMap') {
								arMat.bumpMap = arMat.roughnessMap = (texture);
								console.log('bumpMap' + matNo + '+ roughnessMap');
							} else {
								arMat.bumpMap = (texture);
								console.log('bumpMap' + matNo + 'just bumpMap');
							}
							texture.flipY = true;
							texture.anisotropy = 4;
							texture.repeat.set(pcMat.bumpMap_repeat, pcMat.bumpMap_repeat);
							texture.wrapS = texture.wrapT = 1000;
							stm[matNo].bumpMap = pcm[matNo].bumpMap;
							ion.requestRender();
						},
							function (xhr) {
							console.log((xhr.loaded / xhr.total * 100) + '% loaded');
						})
					} else {
						console.log('bumpMap_' + matNo + '//strange case');
					}
				} else {
					stm[matNo].bumpMap = pcm[matNo].bumpMap;
					console.log('bumpMap_' + matNo + ' no change');
				}
			} else {
				console.log('bumpMap_' + matNo + '//null');
			}

			if (roughnessTex != undefined || null) {
				if (pcm[matNo].roughnessMap != stm[matNo].roughnessMap) {
					if (roughnessTex == 'albedo' || roughnessTex == 'bumpMap') {
						stm[matNo].roughnessMap = pcm[matNo].roughnessMap;
						console.log('roughnessMap_' + matNo + ' //skipped');
					} else if (roughnessTex.indexOf("jpg") !== -1 || roughnessTex.indexOf("png") !== -1) {
						tex_loader.load(roughnessTex,
							function (texture) {
							if (bumpTex == 'bumpMap') {
								arMat.bumpMap = arMat.roughnessMap = (texture);
								console.log('roughnessMap' + matNo + '+ bumpMap');
							} else {
								arMat.roughnessMap = (texture);
								console.log('roughnessMap' + matNo + 'just roughnessMap');
							}
							texture.flipY = true;
							texture.anisotropy = 4;
							texture.repeat.set(pcMat.bumpMap_repeat, pcMat.bumpMap_repeat);
							texture.wrapS = texture.wrapT = 1000;
							stm[matNo].roughnessMap = pcm[matNo].roughnessMap;
							ion.requestRender();
						},
							function (xhr) {
							console.log((xhr.loaded / xhr.total * 100) + '% loaded');
						})
					} else {
						console.log('roughnessMap_' + matNo + '//strange case');
					}
				} else {
					stm[matNo].roughnessMap = pcm[matNo].roughnessMap;
					console.log('roughnessMap_' + matNo + ' no change');
				}
			} else {
				console.log('roughnessMap_' + matNo + '//null');
			}

			
			if (pcMat.color != undefined || null ) {
				var h = pcMat.color.h;
				var s = pcMat.color.s;
				var l = pcMat.color.l;
				arMat.color.setHSL(h,s,l) ;
			} else {
				arMat.color.setHSL(0,0,0.5) ;
			}
			
			if ( bumpTex != null && pcMat.bumpScale != undefined || null) {
				arMat.bumpScale = pcMat.bumpScale;
			} else {
				arMat.bumpScale = 0;
			}
			
			if (pcMat.envMapIntensity != undefined || null ) {
				arMat.envMapIntensity = pcMat.envMapIntensity;
			} else {
				arMat.envMapIntensity = 1;
			}
			
			if (pcMat.metalness != undefined || null ) {
				arMat.metalness = pcMat.metalness;
			} else {
				arMat.metalness = 0;
			}
			
			if (pcMat.roughness != undefined || null ) {
				arMat.roughness = pcMat.roughness;
			} else {
				arMat.roughness = 0.5;
			}
			
			if (arMat.type == "MeshPhysicalMaterial" ) {
				if (pcMat.clearCoat != undefined || null ) {
					arMat.clearCoat = pcMat.clearCoat;
				} else {
					arMat.clearCoat = 0;
				}
				
				if (pcMat.clearCoatRoughness != undefined || null ) {
					arMat.clearCoatRoughness = pcMat.clearCoatRoughness;
				} else {
					arMat.clearCoatRoughness = 0;
				}
				
				if (pcMat.reflectivity != undefined || null ) {
					arMat.reflectivity = pcMat.reflectivity;
				} else {
					arMat.reflectivity = 0.5;
				}
			} else {
			}
			
			this.applyMaterials();
			this.requestRender();
			
		} else {
		}	

		
		if (matNoo = 'mat_03', pcm[matNoo] != undefined || null)   {
			
			let matNo = matNoo;
			let matAr = matNo.substring(4,6);
			let pcMat = pcm[matNo];
			let stMat = stm[matNo];
			let arMat = ion.sc.materials.mm_procedural[matAr-1];
			let albedoTex = pcMat.map;
			let roughnessTex = pcMat.roughnessMap;
			let bumpTex = pcMat.bumpMap;
			
			if (albedoTex != undefined || null) {
				if (pcm[matNo].map != stm[matNo].map) {
					tex_loader.load(albedoTex,
						function (texture) {
						if (bumpTex == 'albedo' && roughnessTex == 'albedo') {
							arMat.map = arMat.bumpMap = arMat.roughnessMap = (texture);
							console.log('albedo' + matNo + 'for all');
						} else if (bumpTex == 'albedo' && roughnessTex !== 'albedo') {
							arMat.map = arMat.bumpMap = (texture);
							console.log('albedo' + matNo + '+ bump');
						} else if (bumpTex !== 'albedo' && roughnessTex == 'albedo') {
							arMat.map = arMat.roughnessMap = (texture);
							console.log('albedo' + matNo + '+ roughnessMap');
						} else {
							arMat.map = (texture);
							console.log('albedo' + matNo + 'just map');
						}
						texture.flipY = true;
						texture.anisotropy = 4;
						texture.repeat.set(pcMat.map_repeat, pcMat.map_repeat);
						texture.wrapS = texture.wrapT = 1000;
						stm[matNo].map = pcm[matNo].map;
						ion.requestRender();
					},
						function (xhr) {
						console.log((xhr.loaded / xhr.total * 100) + '% loaded');
					});
				} else {
					console.log('albedo_' + matNo + ' no change');
				}
			} else {
				console.log('albedo ' + matNo + ' null');
			}

			if (bumpTex != undefined || null) {
				if (pcm[matNo].bumpMap != stm[matNo].bumpMap) {
					if (bumpTex == 'albedo') {
						stm[matNo].bumpMap = pcm[matNo].bumpMap;
						console.log('bumpMap_' + matNo + ' //skipped');
					} else if (bumpTex.indexOf("jpg") !== -1 || bumpTex.indexOf("png") !== -1) {
						tex_loader.load(bumpTex,
							function (texture) {
							if (roughnessTex == 'bumpMap') {
								arMat.bumpMap = arMat.roughnessMap = (texture);
								console.log('bumpMap' + matNo + '+ roughnessMap');
							} else {
								arMat.bumpMap = (texture);
								console.log('bumpMap' + matNo + 'just bumpMap');
							}
							texture.flipY = true;
							texture.anisotropy = 4;
							texture.repeat.set(pcMat.bumpMap_repeat, pcMat.bumpMap_repeat);
							texture.wrapS = texture.wrapT = 1000;
							stm[matNo].bumpMap = pcm[matNo].bumpMap;
							ion.requestRender();
						},
							function (xhr) {
							console.log((xhr.loaded / xhr.total * 100) + '% loaded');
						})
					} else {
						console.log('bumpMap_' + matNo + '//strange case');
					}
				} else {
					stm[matNo].bumpMap = pcm[matNo].bumpMap;
					console.log('bumpMap_' + matNo + ' no change');
				}
			} else {
				console.log('bumpMap_' + matNo + '//null');
			}

			if (roughnessTex != undefined || null) {
				if (pcm[matNo].roughnessMap != stm[matNo].roughnessMap) {
					if (roughnessTex == 'albedo' || roughnessTex == 'bumpMap') {
						stm[matNo].roughnessMap = pcm[matNo].roughnessMap;
						console.log('roughnessMap_' + matNo + ' //skipped');
					} else if (roughnessTex.indexOf("jpg") !== -1 || roughnessTex.indexOf("png") !== -1) {
						tex_loader.load(roughnessTex,
							function (texture) {
							if (bumpTex == 'bumpMap') {
								arMat.bumpMap = arMat.roughnessMap = (texture);
								console.log('roughnessMap' + matNo + '+ bumpMap');
							} else {
								arMat.roughnessMap = (texture);
								console.log('roughnessMap' + matNo + 'just roughnessMap');
							}
							texture.flipY = true;
							texture.anisotropy = 4;
							texture.repeat.set(pcMat.bumpMap_repeat, pcMat.bumpMap_repeat);
							texture.wrapS = texture.wrapT = 1000;
							stm[matNo].roughnessMap = pcm[matNo].roughnessMap;
							ion.requestRender();
						},
							function (xhr) {
							console.log((xhr.loaded / xhr.total * 100) + '% loaded');
						})
					} else {
						console.log('roughnessMap_' + matNo + '//strange case');
					}
				} else {
					stm[matNo].roughnessMap = pcm[matNo].roughnessMap;
					console.log('roughnessMap_' + matNo + ' no change');
				}
			} else {
				console.log('roughnessMap_' + matNo + '//null');
			}

			
			if (pcMat.color != undefined || null ) {
				var h = pcMat.color.h;
				var s = pcMat.color.s;
				var l = pcMat.color.l;
				arMat.color.setHSL(h,s,l) ;
			} else {
				arMat.color.setHSL(0,0,0.5) ;
			}
			
			if ( bumpTex != null && pcMat.bumpScale != undefined || null) {
				arMat.bumpScale = pcMat.bumpScale;
			} else {
				arMat.bumpScale = 0;
			}
			
			if (pcMat.envMapIntensity != undefined || null ) {
				arMat.envMapIntensity = pcMat.envMapIntensity;
			} else {
				arMat.envMapIntensity = 1;
			}
			
			if (pcMat.metalness != undefined || null ) {
				arMat.metalness = pcMat.metalness;
			} else {
				arMat.metalness = 0;
			}
			
			if (pcMat.roughness != undefined || null ) {
				arMat.roughness = pcMat.roughness;
			} else {
				arMat.roughness = 0.5;
			}
			
			if (arMat.type == "MeshPhysicalMaterial" ) {
				if (pcMat.clearCoat != undefined || null ) {
					arMat.clearCoat = pcMat.clearCoat;
				} else {
					arMat.clearCoat = 0;
				}
				
				if (pcMat.clearCoatRoughness != undefined || null ) {
					arMat.clearCoatRoughness = pcMat.clearCoatRoughness;
				} else {
					arMat.clearCoatRoughness = 0;
				}
				
				if (pcMat.reflectivity != undefined || null ) {
					arMat.reflectivity = pcMat.reflectivity;
				} else {
					arMat.reflectivity = 0.5;
				}
			} else {
			}
			
			this.applyMaterials();
			this.requestRender();
			
		} else {
		}	
		
	},
	
	applyMaterials : function() {
	
        var o = this.sc.objects;
		// applies materials if replacement is needed

	},

	
	collectData: function() {
        var serials = this.ion.skus2,
            serialsByTypes = this.serialsByTypes = {};

        // serials
        for (var id in serials) {
            var model = serials[id];

            serialsByTypes[model.type] = serialsByTypes[model.type] || [];
            serialsByTypes[model.type].push({title: model.title || id, id: id});
        }
			// console.log("mat_name : " + mat_name);
			// console.log( "printout:" + JSON.stringify( this.viewer.mat_lib ) );
        }
		

};