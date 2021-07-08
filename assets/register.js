
document.getElementById('registration_form_imgFile_file').classList.add('form-control');
document.getElementById('registration_form_imgFile_file').setAttribute('id' , 'formFile');


document.getElementById('registration_form_imgFile_file').addEventListener('change', function () {
    let newFile = document.getElementById('registration_form_imgFile_file').files[0];


    let fileSizeInMegabytes = newFile.size > 1024 * 1024;
    let fileSize = fileSizeInMegabytes ? newFile.size / (1024 * 1024) : newFile.size / 1024;
    document.getElementById('registration_form_imgFile_file').innerText = newFile.name + ' (' + fileSize.toFixed(2) + ' ' + (fileSizeInMegabytes ? 'MB' : 'KB') + ')';
})
