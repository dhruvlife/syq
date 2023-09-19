// the semi-colon before function invocation is a safety net against concatenated
// scripts and/or other plugins which may not be closed properly.
;// noinspection JSUnusedLocalSymbols
(function ($, window, document, undefined) {

	"use strict";

	setTimeout( function() {
		// Init Group fields. Clone group fields if minimum more than 1.
		$( 'div.forminator-all-group-copies' ).each( function() {
			const groupField = $( this ),
				form = groupField.closest( 'form.forminator-custom-form' ),
				firstBlock = groupField.find( '>.forminator-grouped-fields:first-child' ),
				fieldOptions = firstBlock.data('options');

			if ( ! fieldOptions.is_repeater ) {
				return;
			}

			if ( 'variable' === fieldOptions.min_type ) {
				const dependMinFromField = form.find( '[name="' + fieldOptions.min + '"]' );

				// Add handler on changing min limit field.
				dependMinFromField.on( 'change', function( e ) {
					const changedMinField = $( e.target );
					forminatorChangedRepeaterMin( groupField, forminatorUtils().get_field_value( changedMinField ) );
				} ).trigger( 'change' );
			} else {
				forminatorChangedRepeaterMin( groupField, fieldOptions.min );
			}

			if ( 'variable' === fieldOptions.max_type ) {
				const dependMaxFromField = form.find( '[name="' + fieldOptions.max + '"]' );

				// Add handler on changing max limit field.
				dependMaxFromField.on( 'change', function( e ) {
					const changedMaxField = $( e.target );
					forminatorChangedRepeaterMax( groupField, forminatorUtils().get_field_value( changedMaxField ) );
				} );
			}

			forminatorHideIrrelevantActions( fieldOptions, groupField );

		} );
	}, 100 );

	// Click on Add Item \ Remove Item.
	$( 'form.forminator-custom-form' ).on( 'click', '.forminator-repeater-remove, .forminator-repeater-add', function( e ) {
		e.stopImmediatePropagation();
		e.preventDefault();

		const actionButton = $( this ),
			currentBlock = actionButton.closest( '.forminator-grouped-fields' ),
			fieldOptions = currentBlock.data('options'),
			groupField = actionButton.closest( '.forminator-all-group-copies' ),
			firstBlock = groupField.find( '>.forminator-grouped-fields:first-child' );

		// Click Add Item.
		if ( actionButton.hasClass( 'forminator-repeater-add' ) ) {
			forminatorCloneItem( groupField );
		}

		// Click Remove Item.
		if ( actionButton.hasClass( 'forminator-repeater-remove' ) ) {
			forminatorRemoveItem( currentBlock, fieldOptions );
		}

		forminatorHideIrrelevantActions( fieldOptions, groupField );
	});

	/**
	 * Remove item
	 */
	function forminatorRemoveItem( removingBlock, fieldOptions ) {
		const groupField = removingBlock.closest( '.forminator-all-group-copies' ),
			blockAmount = groupField.find( '>.forminator-grouped-fields' ).length,
			form = groupField.closest( 'form.forminator-custom-form' ),
			min = forminatorGetMin( fieldOptions, form );

		if ( min >= blockAmount ) {
			return false;
		}

		// If removing the first element
		if ( ! removingBlock.prev().length ) {
			// The first Block isn't possible to remove because visibility conditions of other fields can be based on this fields.
			return false;
		}

		removingBlock.remove();

		form.trigger( 'forminator:recalculate' );
	}


	/**
	 * Clone item
	 */
	function forminatorCloneItem( groupField ) {
		const firstBlock = groupField.find( '>.forminator-grouped-fields:first-child' ),
			fieldOptions = firstBlock.data('options'),
			form = groupField.closest( 'form.forminator-custom-form' ),
			blockAmount = groupField.find( '>.forminator-grouped-fields' ).length,
			max = forminatorGetMax( fieldOptions, form );

		if ( ! firstBlock.length || max <= blockAmount ) {
			return false;
		}
		const newBlock = forminatorPrepareCloningBlock( firstBlock );
		groupField.append( newBlock );

		var pattern = new RegExp('((?:calculation|number|currency|radio|select|checkbox)-\\d+)', 'g');
		var matches;

		const names = newBlock.find('[name]');
		names.each(function () {
			while( matches = pattern.exec( $(this).attr('name') ) ) {
				const selector  = matches[1],
					groupCalculation = form.find("[data-formula*='" + selector + "-*']");
				if ( groupCalculation.length ) {
					$(this).on( 'change', function (e) {
						form.trigger( 'forminator:recalculate' );
					});
				}
			}
		});

		newBlock.trigger( 'forminator-clone-group' );
		form.trigger( 'forminator:recalculate' );
	}

	/**
	 * Prepare block for cloning
	 */
	function forminatorPrepareCloningBlock( baseBlock ) {
		const newSuffix = String( Date.now().toString(32) + Math.random().toString(16) ).replace(/\./g, ''),
				form = baseBlock.closest( 'form.forminator-custom-form' ),
				grouId = baseBlock.closest( 'div[id^="group-"]' ).prop( 'id' ),
				formId = form.data( 'form-id' );

		let newBlock = baseBlock.clone();

		newBlock.find( '.select2-container, .forminator-error-message' ).remove();

		// Cloning Rich-Text editors.
		newBlock.find( '.wp-editor-wrap' ).each( function() {
			let textarea = $( this ).find( 'textarea' );
			textarea.css( 'display', 'block' );
			$( this ).replaceWith( textarea );
		} );

		// Cloning Singular File Upload.
		newBlock.find( '.forminator-file-upload [data-empty-text]' ).each( function() {
			let text = $( this ).data( 'empty-text' ) || '';
			$( this ).text( text );
		} );

		// Unselect options in Multiselect.
		newBlock.find( '.forminator-multiselect' ).each( function (j, multiSelect) {
			$(multiSelect).find('input[type="checkbox"]').each(function (i, val) {
				if( $(val).attr('checked' ) ) {
					$(val).closest('label').addClass('forminator-is_checked');
				} else {
					$(val).closest('label').removeClass('forminator-is_checked');
				}
			});
		});

		// Remove selected files for Multiple Upload fields.
		newBlock.find( '.forminator-uploaded-files.forminator-has-files' ).html('');

		// Change id and name attributes.
		let newHtml = newBlock.html().replace( /(id=|name=|for=|data-element=|wp.editor.initialize\()"([^"]+?)(\[\]|-multiselect-default-values|-label)?"/g, '$1"$2-' + newSuffix + '$3"' );

		// for cloning Multiple Upload fields.
		newHtml = newHtml.replace( /(forminator-upload-file--forminator-field-upload-|upload-container-upload-)([^" ]+)/g, '$1$2-' + newSuffix );
		const regexp = new RegExp( `(forminator-field-upload-)([^"]+?)(-${formId})`, 'g' );
		newHtml = newHtml.replace( regexp, '$1$2-' + newSuffix + '$3' );

		newHtml = newHtml.replace( /hasDatepicker|forminator-has_error/g, '' );

		newHtml = forminatorUpdateCalculationFormulas( newHtml, newSuffix, baseBlock );

		const copyInput = '<input name="' + grouId + '-copies[]" type="hidden" value="' + newSuffix + '" />';
		newBlock.html( copyInput + newHtml );
		newBlock.data( 'suffix', newSuffix );

		return newBlock;
	}

	function forminatorUpdateCalculationFormulas( newHtml, newSuffix, baseBlock ) {
		const groupFields = baseBlock.find( '[name]' ).map(function() {
			return this.name;
		}).get();

		$.each( groupFields, function( index, fieldName ) {
			fieldName = fieldName.replace( '[]', '' );
			const regexp = new RegExp( `{${fieldName}}`, 'g' );
			newHtml = newHtml.replace( regexp, '{' + fieldName + '-' + newSuffix + '}' );
		} );


		return newHtml;
	}

	/**
	 * Add new group items if min limit field is increased
	 */
	function forminatorChangedRepeaterMin( groupField, newMin ) {
		const itemAmount = groupField.find('>.forminator-grouped-fields').length,
				firstBlock = groupField.find( '>.forminator-grouped-fields:first-child' ),
				fieldOptions = firstBlock.data('options'),
				diff = newMin - itemAmount;

		// Add Group items.
		for ( var i = 0; i < diff; i++ ) {
			forminatorCloneItem( groupField );
		}

		forminatorHideIrrelevantActions( fieldOptions, groupField );
	}

	/**
	 * Remove group items if max limit field is decreased
	 */
	function forminatorChangedRepeaterMax( groupField, newMax ) {
		const itemAmount = groupField.find('>.forminator-grouped-fields').length,
				firstBlock = groupField.find( '>.forminator-grouped-fields:first-child' ),
				fieldOptions = firstBlock.data('options'),
				diff = itemAmount - newMax;

		if ( 1 > newMax ) {
			return false;
		}

		// Remove Group items.
		for ( var i = 0; i < diff; i++ ) {
			const lastBlock = groupField.find('>.forminator-grouped-fields:last-child');

			forminatorRemoveItem( lastBlock, lastBlock.data('options') );
		}

		forminatorHideIrrelevantActions( fieldOptions, groupField );
	}

	/**
	 * Get min limit
	 */
	function forminatorGetMin( fieldOptions, form ) {
		let min = fieldOptions.min;
		if ( 'variable' === fieldOptions.min_type ) {
			const dependFromField = form.find( '[name="' + min + '"]' );

			min = forminatorUtils().get_field_value( dependFromField );
		}

		return Math.max( 1, min );
	}

	/**
	 * Get max limit
	 */
	function forminatorGetMax( fieldOptions, form ) {
		const min = forminatorGetMin( fieldOptions, form );
		let max = fieldOptions.max;
		if ( 'variable' === fieldOptions.max_type ) {
			const dependFromField = form.find( '[name="' + fieldOptions.max + '"]' );

			max = forminatorUtils().get_field_value( dependFromField );
		}

		return Math.max( 1, max, min );
	}

	/**
	 * Hide impossible action buttons
	 */
	function forminatorHideIrrelevantActions( fieldOptions, groupField ) {
		const form = groupField.closest( 'form.forminator-custom-form' ),
			min = forminatorGetMin( fieldOptions, form ),
			max = forminatorGetMax( fieldOptions, form ),
			items = groupField.find( '>.forminator-grouped-fields' ),
			addButtons = items.find( '>.forminator-action-buttons .forminator-repeater-add' ),
			removeButtons = items.find( '>.forminator-action-buttons .forminator-repeater-remove' ),
			blockAmount = items.length;

		if ( blockAmount >= max ) {
			addButtons.hide();
		} else {
			addButtons.show();
		}

		if ( blockAmount <= min ) {
			removeButtons.hide();
		} else {
			removeButtons.show();
			// The first Block isn't possible to remove because visibility conditions of other fields can be based on this fields.
			$( removeButtons[0] ).hide();
		}

	}

})(jQuery, window, document);
