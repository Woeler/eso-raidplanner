document.addEventListener("DOMContentLoaded", function () {
    let pollAddOptionButtons = document.getElementsByClassName('add_poll_option_link');
    for (let l = 0; l < pollAddOptionButtons.length; l++) {
        pollAddOptionButtons[l].addEventListener("click", function () {
            addFormToCollection(this.dataset.collectionHolderClass);
        });
    }
    let pollOptions = document.querySelectorAll('ul.options li');
    for (let m = 0; m < pollOptions.length; m++) {
        addTagFormDeleteLink(pollOptions[m]);
    }
});

function addFormToCollection(collectionHolderClass) {
    let collectionHolder = document.getElementsByClassName(collectionHolderClass)[0];
    let prototype = collectionHolder.dataset.prototype;
    let index = collectionHolder.dataset.index;
    let newForm = prototype;
    newForm = newForm.replace(/__name__/g, index);
    collectionHolder.dataset.index = parseInt(index) + 1;
    let newFormLi = document.createElement('li');
    newFormLi.innerHTML = newForm;
    newFormLi.className = 'mb-3';
    addTagFormDeleteLink(newFormLi);
    collectionHolder.appendChild(newFormLi)
}

function addTagFormDeleteLink(tagFormLi) {
    let removeFormButton = document.createElement('button');
    removeFormButton.type = 'button';
    removeFormButton.className = 'btn btn-danger';
    removeFormButton.textContent = 'Remove this option';
    tagFormLi.appendChild(removeFormButton);
    removeFormButton.addEventListener('click', function(e) {
        tagFormLi.remove();
    });
}
