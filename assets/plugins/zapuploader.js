/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS
 */

/**
 * @author zap
 */


/**
 *
 * @param id
 * @param options
 * @constructor
 */
function ZAPUploader(id,options){
    this.options = {
        url : null,
        method : 'post',
        uploadMultiple:false,
        directoryUpload:false,
        chunking:false,
        chunkSize:5000000,
        maxFileSize:null, //MB
        previewContainer:null,
        messageContainer:null,
        maxFiles:0, //最大上传文件数量
        headers: {},
        customFormData:{},
        acceptedFiles:null,
        allowedExtensions:'.*',
        autoUpload:true,
        ignoreBadFiles:false,
        skipInvalidFile:false,
        queueSize:5,
        dragoverClass:'highlight',
        previewTemplate:null,
        progress: function(total,fileNumber,percent){
            if($$this.progressBar){
                $$this.progressBar.nodeName === "PROGRESS" ? $$this.progressBar.value = total : $$this.progressBar.style.width = total + '%';
            }
        },
        processing:function(file,i){},
        success:function(i,responseText){},
        uploadStart:function(){
            const progress = $$this.dropArea.querySelector('.zap-progress');
            if(progress){
                progress.style.display = '';
            }
        },
        complete:function(){
            // $$this.options.error(xhr.status,'文件上传错误 ' + xhr.responseText);
        },
        error:function(id,msg){
            if($$this.messageContainer === null) return;
            if((id || null) === null){
                $$this.messageContainer.innerHTML = '';
                return;
            }
            strong = document.createElement('strong');
            strong.classList.value = 'text-danger fw-bold d-block';
            strong.textContent = msg;
            $$this.messageContainer.appendChild(strong)
        },
        addedfile:function(file,index){},
        addfile:function(file){},
        preview:function(file,index){}
    };
    if(typeof options !== 'undefined'){
        for (const name in options) {
            this.options[name] = options[name];
        }
    }
    if(typeof id === "string" && id[0] === '#'){
        this.dropArea = document.querySelector(id);
    }else if(typeof id === "string" && id[0] === '.'){
        var nodeList = document.querySelectorAll(id);
        nodeList.forEach((value,key)=>{
            if(key===0){
                this.dropArea = value;
            }else{
                new ZAPUploader(value,options);
            }
        })
    }else if(typeof id === "object"){
        this.dropArea = id;
    }
    if(this.dropArea === null){
        throw new Error('绑定 ID#'+id+' 失败!');
    }
    var $$this = this;
    if(this.options.previewTemplate === null){
        this.options.previewTemplate = this.createElement(`<div><div class="zap-file-details">
                <img class="zap-file-thumb" />
                <span class="zap-file-name"></span><br/>
                <span class="zap-file-size"></span><br/>
                <span class="zap-file-progress"></span>
                <div class="zap-file-success-mark"><span>✔</span></div>
                <div class="zap-file-error-mark"><span>✘</span></div>
            </div></div>`);
    }else if(typeof this.options.previewTemplate === 'string'){
        this.options.previewTemplate = this.createElement(this.options.previewTemplate);
    }
    this.progressPercent = 0;
    this.uploadProgress = []
    this.fileNumber = 0;
    this.handlerFileNumber = 0;
    this.fileData = [];
    this.previewItems = [];
    this.inputFileElement = this.dropArea.querySelector('input[type=\'file\']');
    this.messageContainer = (this.options.messageContainer === null) ? this.dropArea.querySelector('.zap-message') : document.querySelector(this.options.messageContainer);
    this.previewContainer = (this.options.previewContainer === null) ? this.dropArea.querySelector('.zap-preview') : document.querySelector(this.options.previewContainer);
    this.progressBar = this.dropArea.querySelector('.zap-progress-bar');
    const preventDefaults = function (e) {
        e.preventDefault();
        e.stopPropagation();
    };
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        this.dropArea.addEventListener(eventName, preventDefaults, false)
        document.body.addEventListener(eventName, preventDefaults, false)
    });

    ['dragenter', 'dragover'].forEach(eventName => {
        this.dropArea.addEventListener(eventName, function(e) {
            $$this.dropArea.classList.add($$this.options.dragoverClass)
        }, false)
    });

    ['dragleave', 'drop'].forEach(eventName => {
        this.dropArea.addEventListener(eventName, function(e) {
            $$this.dropArea.classList.remove($$this.options.dragoverClass)
        }, false)
    });
    //bind input file
    this.inputFileElement.addEventListener('change',function(e){
        if (!this.files.length){return;}
        $$this.initProgress();
        $$this.clearPreviewContainer();
        $$this.options.addfile(e); //触发事件
        if ($$this.options.maxFiles!==0 && this.files.length > $$this.options.maxFiles)
        {
            $$this.options.error(4,'文件上传数量超过最大限制');
            return;
        }
        for (var i = 0; i< this.files.length; i++) {
            if($$this.checkFile(this.files[i])) {
                $$this.addFile({file:this.files[i],path:undefined})
            }
        }
        this.value = '';
        $$this.startUpload();
    },false);
    if(!this.inputFileElement.hasAttribute('accept')){
        this.inputFileElement.setAttribute('accept',options.allowedExtensions.replaceAll('|',','));
    }

    // Handle dropped files
    this.dropArea.addEventListener('drop', function(e) {
        $$this.clear();
        $$this.initProgress();
        $$this.clearPreviewContainer();
        $$this.options.addfile(e);
        if($$this.options.directoryUpload){
            if(e.dataTransfer && e.dataTransfer.items)
            {
                var items = e.dataTransfer.items;
                for (let i=0; i<items.length; i++) {
                    var item = items[i].webkitGetAsEntry();
                    if (item) {
                        $$this.addDirectory(item);
                    }
                }
                return;
            }
            $$this.startUpload();
        }

        var files = e.target.files || e.dataTransfer.files;
        if (!files.length)
        {
            $$this.options.error(2,'文件类型不支持');
            return;
        }

        if ($$this.options.maxFiles!==0 && files.length > $$this.options.maxFiles)
        {
            $$this.options.error(4,'文件上传数量超过最大限制');
            return;
        }

        for (var i = 0; i< files.length; i++) {
            if(files[i].type === ""){
                $$this.options.error(1,'不支持上传目录');
                if(!$$this.options.skipInvalidFile){
                    $$this.clearPreviewContainer();
                    return;
                }
            }
            if($$this.checkFile(files[i])){
                $$this.fileNumber++;
                $$this.addFile({file:files[i],path:undefined})
            }
        }
        $$this.startUpload();
    }, false)

}

ZAPUploader.prototype = {
    progress:function(total,fileNumber,percent){
        if(this.progressBar){
            this.progressBar.nodeName === "PROGRESS" ? this.progressBar.value = total : this.progressBar.style.width = total + '%';
        }
        this.options.progress(total,fileNumber,percent);
    },
    addFile: function(fileItem) {
        // this.fileData.length
        this.fileData.push(fileItem);
        const index = this.fileData.length - 1;
        const file = fileItem.file;
        $this = this;
        //add file event
        this.options.addedfile({file:file,name:file.name,size:file.size},index);
        if(!this.previewContainer){
            return;
        }
        var previewFile = this.options.previewTemplate.querySelector('.zap-file-details').cloneNode(true)
        previewFile.classList.add('zap-filenumber-'+index)
        // var previewFile = this.createDiv(this.options.previewTemplate,{'class':'zap-file-item','id':'zap-file-item-'+index});
        previewFile.querySelectorAll('.zap-file-name').forEach(function(element){
            element.innerHTML = file.name;
        });

        previewFile.querySelectorAll('.zap-file-size').forEach(function(element){
            element.innerHTML = $this.humanFileSize(file.size );
        });

        previewFile.querySelectorAll('.zap-file-thumb').forEach(function(element){
            let fileReader = new FileReader();
            fileReader.onload = () => {
                element.src = fileReader.result;
            };
            fileReader.readAsDataURL(file);
        });
        this.previewItems[index] = previewFile;
        this.previewContainer.appendChild(previewFile);
    },
    createDiv : function (str,attributes) {
        let div = document.createElement("div");
        for (const prop in attributes) {
            div.setAttribute(prop,attributes[prop]);
        }
        if(typeof str === 'string'){
            div.innerHTML = str;
        }else{
            div.appendChild(str);
        }
        return div;
    },
    createElement : function (str) {
        let div = document.createElement("div");
        div.innerHTML = str;
        return div.childNodes[0];
    },
    initProgress:function(){
        const progress = this.dropArea.querySelector('.zap-progress');
        if(progress){
            progress.style.display = 'none';
        }
        if(this.progressBar){
            this.progressBar.nodeName === "PROGRESS" ? this.progressBar.value = 0 : this.progressBar.style.width = '0%';
        }
    },
    clearPreviewContainer:function(){
        if(this.previewContainer !== null) {
            this.previewContainer.innerHTML = '';
        }
    },
    humanFileSize:function(bytes) {
        if (bytes === 0) { return "0.00 B"; }
        var e = Math.floor(Math.log(bytes) / Math.log(1024));
        return (bytes/Math.pow(1024, e)).toFixed(2)+' '+' KMGTP'.charAt(e)+'B';
    }
};


ZAPUploader.prototype.addDirectory = function (item) {
    var $$this = this;
    if (item.isDirectory) {
        var directoryReader = item.createReader();
        directoryReader.readEntries(function(entries) {
            entries.forEach(function(entry) {
                $$this.addDirectory(entry);
            });
        });
    } else {
        item.file(function(file){
            if($$this.checkFile(file)){
                $$this.fileNumber++;
                $$this.addFile({file:file,path:item.fullPath})
            }
        });

    }
}

ZAPUploader.prototype.clear = function () {
    this.progressPercent = 0
    this.uploadProgress = []
    this.fileNumber = 0
    this.handlerFileNumber = 0;
    this.fileData.length = 0;
}

ZAPUploader.prototype.startUpload = function () {

    if(this.options.autoUpload){
        this.processQueue();
    }
}

ZAPUploader.prototype.processQueue = function () {
    this.options.uploadStart();
    this.uploadProgress = new Array(this.fileData.length)
    this.uploadProgress.fill(0);
    this.fileNumber = this.fileData.length;

    console.log('processQueue',this.fileData.length,this.fileData)
    for (let i = 0; i < this.fileData.length; i++) {
        console.log("process",this.fileData[i])
        this.uploadFile(this.fileData[i].file,i,this.fileData[i].path);
    }
}

ZAPUploader.prototype.updateProgress = function(fileNumber, percent) {
    this.uploadProgress[fileNumber] = percent;
    let total = this.uploadProgress.reduce((tot, curr) => tot + curr, 0) / this.uploadProgress.length;
    this.progressPercent = total;
    //文件显示上传进度
    if(this.previewItems[fileNumber]){
        const zfProgress = this.previewItems[fileNumber].querySelectorAll('.zap-file-progress');
        zfProgress.forEach((zfp) => {
            if(zfp.nodeName === 'SPAN'){
                if(percent === 100){
                    zfp.classList.add('zap-msg-success');
                    const successMark = this.options.previewTemplate.querySelector('.zap-file-success-mark').innerHTML;
                    zfp.innerHTML = successMark || '100%';

                } else{
                    zfp.innerHTML = percent.toFixed(2) + '%';
                }
            }else if(zfp.classList.contains('progress-bar')){
                if(percent !== 100 && zfp.parentElement.classList.contains('zap-none')){
                    zfp.parentElement.classList.remove('zap-none');
                }
                zfp.style.width = percent + '%';
                if(percent === 100){
                    zfp.parentElement.classList.add('zap-none');
                }

            }else if(zfp.nodeName === 'PROGRESS'){
                zfp.value = percent;
            }
        });

    }
    this.progress(total,fileNumber,percent,this.previewItems[fileNumber]);
}

ZAPUploader.prototype.complete = function(responseText,data) {
    if(this.progressBar){
        if(this.progressBar.classList.contains('zap-progress') ){
            this.progressBar.classList.add('zap-none');
        }else if(this.progressBar.parentElement.classList.contains('zap-progress')){
            this.progressBar.parentElement.classList.add('zap-none');
        }else{
            this.progressBar.classList.add('zap-none');
        }
    }
    this.options.complete(responseText,data);

}

ZAPUploader.prototype.uploadFile = function(file, i , fullPath) {
    var url = this.options.url;
    var xhr = new XMLHttpRequest()
    var formData = new FormData()
    xhr.open('POST', url, true)
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest')
    for (const headerName in this.options.headers) {
        xhr.setRequestHeader(headerName, this.options.headers[headerName])
    }
    var $$this = this

    xhr.upload.addEventListener("progress", function(e) {
        $$this.updateProgress(i, (e.loaded * 100.0 / e.total) || 100)
    })
    xhr.addEventListener('readystatechange', function(e) {
        if (xhr.readyState === 4 && xhr.status === 200) {
            $$this.updateProgress(i, 100);
            $$this.handlerFileNumber++;
            $$this.options.success(i,xhr.responseText);
            if($$this.handlerFileNumber === $$this.fileNumber){
                $$this.clear();
                $$this.complete(xhr.responseText);
                $$this.options.complete(xhr.responseText,{fileNumber:$$this.fileNumber});
            }
        }
        else if (xhr.readyState === 4 && xhr.status !== 200) {
            $$this.clear();
            $$this.options.error(xhr.status,'文件上传错误 ' + xhr.responseText);
        }
    })

    formData.append('file', file)
    if(typeof fullPath !== 'undefined'){
        formData.append('fullPath', fullPath)
    }
    for (const name in $$this.options.customFormData) {
        formData.append(name, $$this.options.customFormData[name]);
    }
    xhr.send(formData)
    $$this.options.processing(file,i);
}

ZAPUploader.prototype.checkFile = function(file) {
    const fileName = file.name;
    const fileExtRegex = '(' + this.options.allowedExtensions + ')$';
    if(!fileName.match(new RegExp(fileExtRegex,'gi'))){
        if(!this.options.ignoreBadFiles){
            this.options.error(3,fileName + ' 文件格式不支持!');
        }
        return false;
    }
    if(this.options.acceptedFiles !== null){
        const fileMimeType = file.type;
        const fileMimeTypeRegex = '(' + this.options.acceptedFiles.replace(/,/g,"|") + ')$';
        if(!fileMimeType.match(new RegExp(fileMimeTypeRegex,'gi'))){
            if(!this.options.ignoreBadFiles){
                this.options.error(5,fileName + ' 文件格式不支持!');
            }
            return false;
        }
    }

    //check file size
    const fileSize = file.size  / 1024 / 1024;//MB
    if(this.options.maxFileSize !== null && fileSize > this.options.maxFileSize){
        if(!this.options.ignoreBadFiles){
            this.options.error(4,fileName + ' 文件超出最大 '+this.options.maxFileSize+'MB 限制!');
        }
        return false;
    }


    return true;
}



