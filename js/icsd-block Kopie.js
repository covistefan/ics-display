// ES5 style

( function( blocks, element  ) {

	const el = element.createElement;
	const {registerBlockType} = blocks;
    // const {ServerSideRender} = wp.components;
    const {__} = wp.i18n; //translation functions

	const iconCal = el('svg', { width: 20, height: 20 },
		el( 'path',
			{
				d: "M15 4h3v15H2V4h3V3c0-.41.15-.76.44-1.06c.29-.29.65-.44 1.06-.44s.77.15 1.06.44c.29.3.44.65.44 1.06v1h4V3c0-.41.15-.76.44-1.06c.29-.29.65-.44 1.06-.44s.77.15 1.06.44c.29.3.44.65.44 1.06v1zM6 3v2.5a.491.491 0 0 0 .5.5a.491.491 0 0 0 .5-.5V3c0-.14-.05-.26-.15-.35c-.09-.1-.21-.15-.35-.15s-.26.05-.35.15c-.1.09-.15.21-.15.35zm7 0v2.5c0 .14.05.26.14.36c.1.09.22.14.36.14s.26-.05.36-.14c.09-.1.14-.22.14-.36V3a.491.491 0 0 0-.5-.5a.491.491 0 0 0-.5.5zm4 15V8H3v10h14zM7 9v2H5V9h2zm2 0h2v2H9V9zm4 2V9h2v2h-2zm-6 1v2H5v-2h2zm2 0h2v2H9v-2zm4 2v-2h2v2h-2zm-6 1v2H5v-2h2zm4 2H9v-2h2v2zm4 0h-2v-2h2v2z"
			}
		)
	);

	registerBlockType( 'icsd/block', {
		title: 'ICS Display',
        description: 'Select the ICS-Calendar you want to display',
		icon: iconCal,
		category: 'widgets',
		keywords: [ 'calendar', 'covi', 'ics' , 'icsd' ],
        attributes: {
            icsdvalues: {
                type: 'number'
            }
        },

		// The "edit" property must be a valid function.
		edit: function( props ) {

            // here we can place a reference
            // f. e. 
            // var blockProps = wp.blockEditor.useBlockProps();
            var icsdvalues = props.attributes.icsdvalues, children;

            console.log(props);

            function setICSD( event ) {
                var selected = event.target.querySelector( 'option:checked' );
                props.setAttributes( { icsdvalues: selected.value } );

                console.log( selected.value )

                event.preventDefault();
            }

            children = [];
            children.push(
                el( 'label', { 'for' : 'icsd-select' , 'class' : 'components-placeholder__label' }, 'ICS Display' )
            )
            children.push(
                el( 'select', { name : 'icsd-select' , 'class' : 'components-select-control', id : 'icsd-select' , value: icsdvalues , onChange: setICSD }, 
                    el( 'option', null, '- Select -' ),
                    el( 'option', { value: '26' }, 'ICS Name 26' ),
                    el( 'option', { value: '27' }, 'ICS Name 27' ),
                    el( 'option', { value: '28' }, 'ICS Name 28' )
                )
            );

			return (
				el( 'div', { className: props.className },
					el( 'div', { className: 'icsd-block-post-editor icsd-block-page-editor' },
                        el( 'form' , { onSubmit: setICSD } , children ),
					)
				)
			);
		},

		save: function() {
			return null;
		},
	} );
} )(
	window.wp.blocks,
	window.wp.element,
);
