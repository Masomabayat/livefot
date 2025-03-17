

document.addEventListener("DOMContentLoaded", (event) => {
    const allTabs = document.querySelectorAll('.tabs-block__tab');
    const contentWrappers = document.querySelectorAll('.tabs-block__tab-content');
    const changeableContentBoxes = document.querySelectorAll('.tabs-block__changeable-content-box');

    allTabs.forEach((elem) => {
        const elemDataContent = elem.dataset.content;
        const elemDataTab = elem.dataset.tab;

        elem.addEventListener('click', (event) => {
            allTabs.forEach((el) => {
                el.classList.remove('tabs-block__tab_active');
            })
           
            elem.classList.add('tabs-block__tab_active');
            
            contentWrappers.forEach((el) => {
                el.classList.remove('tabs-block__tab-content_active');
            })
            contentWrappers.forEach((el) => {
                const elDataContent = el.dataset.content;
                if(elemDataContent == elDataContent) {
                    el.classList.add('tabs-block__tab-content_active');
                }
            })

            if(elemDataTab) {
                changeableContentBoxes.forEach((el) => {
                    el.classList.remove('tabs-block__changeable-content-box_active');
                })
                changeableContentBoxes.forEach((el) => {
                    const elDataTab = el.dataset.tab;
                    if(elemDataTab == elDataTab) {
                        el.classList.add('tabs-block__changeable-content-box_active');
                    }
                })
            }
        })
    })


    const formVariantWrapper = document.querySelector('.tabs-block__form-variant-wrapper');
    const formVariantSelectedShow = document.querySelector('.tabs-block__form-variant-selected-show');
    const formVariants = document.querySelectorAll('.tabs-block__form-variant');
    const formContentVariants = document.querySelectorAll('.tabs-block__form-wrapper');

    formVariantSelectedShow.addEventListener('click', (event) => {
        formVariantWrapper.classList.toggle('tabs-block__form-variant-wrapper_active');
    })

    formVariants.forEach((elem) => {
        const formVariantData = elem.dataset.form;

        elem.addEventListener('click', (event) => {
            formVariants.forEach((elem) => {
                elem.classList.remove('tabs-block__form-variant_selected');
            })

            elem.classList.add('tabs-block__form-variant_selected');
            formVariantSelectedShow.value = elem.textContent;

            formContentVariants.forEach((el) => {
                const formContentVariantData = el.dataset.form;

                el.classList.remove('tabs-block__form-wrapper_active');
                if(formVariantData == formContentVariantData) {
                    el.classList.add('tabs-block__form-wrapper_active');
                }
            })
        })
    })


    const formSelectBox = document.querySelectorAll('.tabs-block__form-select');

    if(formSelectBox) {

        formSelectBox.forEach((el) => {
        const formSelectShow = el.querySelector('.tabs-block__form-selected');
        const formSearch = el.querySelector('input[type="search"]');
        const formSelectVariantsBox = el.querySelector('.tabs-block__select-variants');
        let formSelectVariants = formSelectVariantsBox.querySelectorAll('.tabs-block__select-variant');

            formSelectShow.addEventListener('click', (event) => {
                el.classList.toggle('tabs-block__form-select_active');
            })

    

            formSelectVariants.forEach((elem) => {
            elem.addEventListener('click', (event) => {
                formSelectVariants.forEach((elem) => {
                    elem.classList.remove('tabs-block__select-variant_selected');
                })
    
                elem.classList.add('tabs-block__select-variant_selected');
                formSelectShow.value = elem.innerText;
            })

            if(formSearch) {
                formSearch.addEventListener('input', (event) => {
                    let val = event.target.value.trim().toLowerCase();
                    if(val != '') {
                        formSelectVariants.forEach((elem) => {
                            if(elem.innerText.trim().toLowerCase().search(val) == -1) {
                                elem.classList.add('hide');
                            } else {
                                elem.classList.remove('hide');
                            }
                        })
                    } else {
                        formSelectVariants.forEach((elem) => {
                            elem.classList.remove('hide');
                        })
                    }
                })
            }


        })
    })  

    }


    const applicationDropDown = document.querySelectorAll('.tabs-block__changeable-content-box');

    if(applicationDropDown) {
        applicationDropDown.forEach((elem) => {
            const dropDownBlocks = elem.querySelectorAll('.tabs-block__tab-text-wrapper');
            dropDownBlocks.forEach((el) => {
                el.addEventListener('click', (event) => {
                    console.log('sada');
                    el.classList.toggle('drop-down_active');
                })
            })
        }) 
    }

    flatpickr(".tabs-block__form-choose-date", {
        dateFormat: "m.d.Y",
    });

    jQuery(document).ready(function ($) {
        $('input[type="submit"]').on('click', function(event) {
            event.preventDefault();
            let form = event.target.closest('form'),
                name = form.querySelector('input[name="user-name"]'),
                email = form.querySelector('.user-email'),
                message = form.querySelector('.user-message'),
                statusText = form.querySelector('.tabs-block__form-status-text'),
                fd = new FormData(form);

            let checkFields = [name, email, message],
                checkCounter = 0;

                checkFields.forEach(elem => {
                    if(elem.value.trim() == '') {
                        elem.style.borderColor = "red";
                        statusText.innerText = 'Please fill in all required fields';
                        statusText.style.color = 'red';
                    } else {
                        elem.style.borderColor = "#CCC";
                        checkCounter += 1;
                    }
                })

            function resetForm() {
                let formElements = form.querySelectorAll('textarea, input[type="text"], input[type="email"], input[type="tel"]');
                formElements.forEach(elem => {
                    elem.value = '';
                })
            }

            
                if(checkCounter == checkFields.length) {
                    statusText.innerText = '';
                    $.ajax({
                        url: fdgajax.ajaxurl,
                        type: 'POST',
                        contentType: false,
                        processData: false,
                        data: fd,
                        dataType: 'text',
                        success: function(data) {
                            if(data) {
                                if(data == 'success') {
                                    statusText.style.color = 'green';
                                    statusText.innerText = 'Your message has been sent'; 
                                }else if(data == 'error'){
                                    statusText.style.color = 'red';
                                    statusText.innerText = 'Your message was not sent';
                                }
                                resetForm();
                                resetGiftName();
                            }
                        },
                        error: function(jqXHR, exception) {
                            statusText.style.color = 'red';
                            statusText.innerText = 'Your message was not sent';
                        }
                    })
                }

                setTimeout(() => {statusText.innerText = '';}, 5000);
        })
    })

    function updateUploadGift() {
        if(!['image/jpeg', 'image/png', 'image/webp', 'image/svg', 'image/jpg'].includes(this.files[0].type)) {
            giftFile.value = '';
            giftName.style.color = 'red';
            giftName.innerText = 'Only images are allowed!';
        } else if (this.files[0].size > 5 * 1024 * 1024) {
            giftFile.value = '';
            giftName.style.color = 'red';
            giftName.innerText = 'Maximum image size 5mb!';
        } else {
            giftName.style.color = '#000';
            giftName.innerText = this.files[0].name;
        }
    }

    function resetGiftName() {
        if(giftName) {
            giftFile.value = '';
            giftName.innerText = 'Add Gift';
        }
    }

    const giftFile = document.querySelector('#gift');
    const giftName = document.querySelector('.gift-support__form-gift-file-name');

    if(giftFile) {
        giftFile.addEventListener('change', updateUploadGift)
    }
});