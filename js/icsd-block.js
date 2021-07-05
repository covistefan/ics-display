// uses parts from https://gist.github.com/rogerlos/ » Dynamic-SelectControl.js

( function() {

    const __ = wp.i18n.__;
    const el = wp.element.createElement;
    const registerBlockType = wp.blocks.registerBlockType;
    
    const ServerSideRender = wp.components.ServerSideRender;
    const SelectControl = wp.components.SelectControl;
    const InspectorControls = wp.blockEditor.InspectorControls;

    const icsdoptions = { icsdvalues: [] };

    const iconCal = el('svg', { width: 20, height: 20 },
        el( 'path',
			{
				d: "M15 4h3v15H2V4h3V3c0-.41.15-.76.44-1.06c.29-.29.65-.44 1.06-.44s.77.15 1.06.44c.29.3.44.65.44 1.06v1h4V3c0-.41.15-.76.44-1.06c.29-.29.65-.44 1.06-.44s.77.15 1.06.44c.29.3.44.65.44 1.06v1zM6 3v2.5a.491.491 0 0 0 .5.5a.491.491 0 0 0 .5-.5V3c0-.14-.05-.26-.15-.35c-.09-.1-.21-.15-.35-.15s-.26.05-.35.15c-.1.09-.15.21-.15.35zm7 0v2.5c0 .14.05.26.14.36c.1.09.22.14.36.14s.26-.05.36-.14c.09-.1.14-.22.14-.36V3a.491.491 0 0 0-.5-.5a.491.491 0 0 0-.5.5zm4 15V8H3v10h14zM7 9v2H5V9h2zm2 0h2v2H9V9zm4 2V9h2v2h-2zm-6 1v2H5v-2h2zm2 0h2v2H9v-2zm4 2v-2h2v2h-2zm-6 1v2H5v-2h2zm4 2H9v-2h2v2zm4 0h-2v-2h2v2z"
			}
		)
	);

    icsd_selectors();

	registerBlockType( 'ics-display/icsd-block', {
		title: __('ICS Display'),
        description: __('Select the ICS-Calendar you want to display'),
		icon: iconCal,
		category: 'widgets',
		keywords: [ __('calendar'), 'covi', 'ics' ],
        attributes: {
            'icsdblock' : {
                type: 'number',
            }
        },

		edit: function( props ) {

            return el('div', {}, [
                //Preview a block with a PHP render callback
                el( ServerSideRender, {
                    block: 'ics-display/icsd-block',
                    attributes: { 
                        icsdblock: props.attributes.icsdblock
                    }
                } ),
                //Block inspector
                el( InspectorControls , {},
                    [
                        el('div', { className: 'block-editor-block-card' } ,
                        //Select ICSD Element
                            icsd_select ( props )
                        )
                    ]
                )
            ]);

		},

		save: function() {
			return null;
		},
	});

    function icsd_select ( props ) {
        return el(
            SelectControl,
            {
                key         : props.clientId + '_select',
                label       : __( 'Choose ICS-Display Source' ),
                options     : typeof( icsdoptions ) === 'undefined' ? [ { key : 'postnone', label : __('No ICS Displays setup'), value : 0 } ] : icsdoptions.icsdvalues,
                value       : props.attributes.icsdblock,
                onChange    : function ( val ) {
                    props.setAttributes( props.attributes.icsdblock = parseInt( val ) )
                    props.setAttributes( { useICS : parseInt( val ) } )
                }
            }
        );
    }

    function icsd_selector_opts() {
        return new Promise(
            function ( resolve ) {
                jQuery.when( jQuery.ajax({
                    url: '/wp-admin/admin-ajax.php',
                    type: 'post',
                    dataType: 'json',
                    data: { 'action': 'get_ics_ajax' },
                    success: function( data ) {
                        return data;
                    },
                    error: function(errorThrown){
                        console.log(errorThrown);
                    }
                })).then( function ( data ) {
                    let opts = [ { key : 'postnone', label : __('Select a ICS Display'), value : 0 , 'disabled' : 'disabled' } ];
                    data.forEach( function ( ICS ) {
                        opts.push( { key: ICS.key, label: ICS.label, value: ICS.value } );
                    } );
                    resolve( opts );
                }
            )}
        )
    }

    function icsd_selectors() {
        icsd_selector_opts().then( opts => { 
            icsdoptions.icsdvalues = opts;
        });
    }

} )(
	window.wp.blocks,
	window.wp.element, 
    window.wp.editor, 
    window.wp.components, 
    window.wp.apiFetch,
    window.wp.ajax
);
