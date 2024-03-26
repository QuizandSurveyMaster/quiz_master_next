/**
 * Select or add a category
 */
import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import {
	PanelBody,
    Button,
    TreeSelect,
	TextControl,
	ToggleControl,
	RangeControl,
	RadioControl,
	SelectControl,
	CheckboxControl,
    Flex,
    FlexItem,
} from '@wordpress/components';
import { qsmIsEmpty, qsmFormData } from '../helper';

const SelectAddCategory = ({
    isCategorySelected,
    setUnsetCatgory
}) => {

    //whether showing add category form
	const [ showForm, setShowForm ] = useState( false );
    //new category name
    const [ formCatName, setFormCatName ] = useState( '' );
    //new category prent id
    const [ formCatParent, setFormCatParent ] = useState( 0 );
    //new category adding start status
    const [ addingNewCategory, setAddingNewCategory ] = useState( false );
    //error
    const [ newCategoryError, setNewCategoryError ] = useState( false );
    //category list 
    const [ categories, setCategories ] = useState( qsmBlockData?.hierarchicalCategoryList );

     //get category id-details object 
     const getCategoryIdDetailsObject = ( categories ) => {
        let catObj = {};
        categories.forEach( cat => {
            catObj[ cat.id ] = cat;
            if ( 0 < cat.children.length ) {
               let childCategory = getCategoryIdDetailsObject( cat.children );
               catObj = { ...catObj, ...childCategory };
            }
        });
        return catObj;
    }

    //category id wise details
    const [ categoryIdDetails, setCategoryIdDetails ] = useState( qsmIsEmpty( qsmBlockData?.hierarchicalCategoryList ) ? {} : getCategoryIdDetailsObject( qsmBlockData.hierarchicalCategoryList ) );

    const addNewCategoryLabel = __( 'Add New Category ', 'quiz-master-next' );
    const noParentOption = `— ${ __( 'Parent Category ', 'quiz-master-next' ) } —`;

    //Add new category
    const onAddCategory = async ( event ) => {
		event.preventDefault();
		if ( newCategoryError || qsmIsEmpty( formCatName ) || addingNewCategory ) {
			return;
		}
        setAddingNewCategory( true );

        //create a page
        apiFetch( {
            url: qsmBlockData.ajax_url,
            method: 'POST',
            body: qsmFormData({
                'action': 'save_new_category',
                'name': formCatName,
                'parent': formCatParent   
            })
        } ).then( ( res ) => {
            if ( ! qsmIsEmpty( res.term_id ) ) {
                let term_id = res.term_id;
                //console.log("save_new_category",res);
                //set category list
                apiFetch( {
                    path: '/quiz-survey-master/v1/quiz/hierarchical-category-list',
                    method: 'POST'
                } ).then( ( res ) => {
                   // console.log("new categorieslist",  res);
                    if ( 'success' == res.status ) {
                        setCategories( res.result );
                        setCategoryIdDetails( res.result );
                         //set form
                        setFormCatName( '' );
                        setFormCatParent( 0 );
                        //set category selected
                        setUnsetCatgory( term_id, getCategoryIdDetailsObject( term.id ) );
                        setAddingNewCategory( false );
                    }
                });
               
            }
            
        });
    }

    //get category name array
    const getCategoryNameArray = ( categories ) => {
        let cats = [];
        categories.forEach( cat => {
            cats.push( cat.name );
            if ( 0 < cat.children.length ) {
               let childCategory = getCategoryNameArray( cat.children );
                cats = [ ...cats, ...childCategory ];
            }
        });
        return cats;
    }

    //check if category name already exists and set new category name
    const checkSetNewCategory = ( catName, categories ) => {
        categories = getCategoryNameArray( categories );
        console.log( "categories", categories );
        if ( categories.includes( catName ) ) {
            setNewCategoryError( catName );
        } else {
            setNewCategoryError( false );
            setFormCatName( catName );
        }
        // categories.forEach( cat => {
        //     if ( cat.name == catName ) {
        //         matchName = true;
        //         return false;
        //     } else if ( 0 < cat.children.length ) {
        //         checkSetNewCategory( catName, cat.children )
        //     }
        // });
        
        // if ( matchName ) {
        //     setNewCategoryError( matchName );
        // } else {
        //     setNewCategoryError( matchName );
        //     setFormCatName( catName );
        // }
    }

    const renderTerms = ( categories ) => {
		return categories.map( ( term ) => {
			return (
				<div
					key={ term.id }
					className="editor-post-taxonomies__hierarchical-terms-choice"
				>
					<CheckboxControl
                        label={ term.name }
                        checked={ isCategorySelected( term.id ) }
                        onChange={ () => setUnsetCatgory( term.id, categoryIdDetails ) }
                    />
					{ !! term.children.length && (
						<div className="editor-post-taxonomies__hierarchical-terms-subchoices">
							{ renderTerms( term.children ) }
						</div>
					) }
				</div>
			);
		} );
	};
    
    return(
        <PanelBody title={ __( 'Categories', 'quiz-master-next' ) } initialOpen={ true }>
            <div
				className="editor-post-taxonomies__hierarchical-terms-list"
				tabIndex="0"
				role="group"
				aria-label={ __( 'Categories', 'quiz-master-next' ) }
			>
				{ renderTerms( categories ) }
			</div>
            <div className='qsm-ptb-1'>
                <Button 
                    variant="link"
                    onClick={ () => setShowForm( ! showForm )	}
                    >
                    { addNewCategoryLabel }
                </Button>
            </div>
            { showForm && (
				<form onSubmit={ onAddCategory }>
					<Flex direction="column" gap="1">
						<TextControl
							__nextHasNoMarginBottom
							className="editor-post-taxonomies__hierarchical-terms-input"
							label={ __( 'Category Name', 'quiz-master-next' ) }
							value={ formCatName }
							onChange={ ( formCatName ) => checkSetNewCategory( formCatName, categories ) }
							required
						/>
						{ 0 < categories.length && (
							<TreeSelect
								__nextHasNoMarginBottom
								label={ __( 'Parent Category', 'quiz-master-next' ) }
								noOptionLabel={ noParentOption }
								onChange={ ( id ) => setFormCatParent( id ) }
								selectedId={ formCatParent }
								tree={ categories }
							/>
						) }
						<FlexItem>
							<Button
								variant="secondary"
								type="submit"
								className="editor-post-taxonomies__hierarchical-terms-submit"
                                disabled={  newCategoryError || addingNewCategory }
							>
								{ addNewCategoryLabel }
							</Button>
						</FlexItem>
                        <FlexItem>
                            <p className='qsm-error-text' >
                                { false !== newCategoryError && __( 'Category ', 'quiz-master-next' ) + newCategoryError + __( ' already exists.', 'quiz-master-next' ) }
                            </p>
                        </FlexItem>
					</Flex>
				</form>
			) }
		</PanelBody>
    );
}

export default SelectAddCategory;