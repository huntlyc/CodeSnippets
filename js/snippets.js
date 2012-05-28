//Some Globals, cause we all love 'em so much
var editorHelper, 
    codeSnippet, 
    snippetUI,
    loadingModal;

var CodeSnippet = {
    _title: "",
    _keywords: [],
    _description: "",
    _links: [],
    _files: [],
    _views: 1,
    _dateCreated: "",
    _lastModified: "",

    init: function () {
        return this;
    },

    setTitle: function(title){
        this._title = snippetHelper.encodeString(title);
    },

    updateKeywords: function(newKeywordList){
        var tmpList,
            regxPatt;
        
        regxPatt = /[^\w]/g;
        
        tmpList = newKeywordList;

        for (var i = 0; i < tmpList.length; i++) {
            tmpList[i] = tmpList[i].replace(regxPatt, "").toLowerCase();
        };

        this._keywords = tmpList;
    },

    setDescription: function(description){
        this._description = snippetHelper.encodeString(description);
    },

    addLink: function (newLink) { 
        this._links.push(snippetHelper.encodeString(newLink));
    },

    removeLink: function(link){
        var arrayPosition = -1;
        for (var i = 0; i < this._links.length; i++) {
            if(this._links[i] === snippetHelper.encodeString(link)){
                arrayPosition = i;
            }
        }

        if(arrayPosition !== -1){ //remove link from array
            this._links.splice(arrayPosition, 1);
        }
    },

    setFiles: function (fileList) {
        this._files = fileList;
    },

    setViews: function(views){
        this._views = views;
    },

    setDateCreated: function(dateCreated){
        this._dateCreated = dateCreated;
    },

    setLastModified: function(lastModified){
        this._lastModified = lastModified;
    },
    
    toJSONString: function () {        
        var snippet, 
            curKeyword,
            curFile, 
            curLink;

        //set title and metadata
        snippet = "{";
        snippet += '"title": "' + this._title + '", ';
        
        //Build Keywords
        snippet += '"keywords": [';        
        if(this._keywords.length > 0){
            for (var i = 0; i < this._keywords.length; i++) {;
                curKeyword = this._keywords[i];
                snippet += '"' + curKeyword + '", ';
            };
            snippet = snippet.substring(0, snippet.length -2);
        }
        snippet += '], ';

        snippet += '"description": "' + this._description + '", ';
        snippet += '"date_created": "' + this._dateCreated + '", ';
        snippet += '"last_modified": "' + this._lastModified + '", ';
        snippet += '"views": ' + this._views + ', ';

        //Build Files
        snippet += '"files": [';        
        if(this._files.length > 0){
            for (var i = 0; i < this._files.length; i++) {;
                curFile = this._files[i];
                snippet += '{"filename": "' + curFile.filename + '", ';
                snippet += '"filecontent": "' + curFile.filecontent + '"}, ';
            };
            snippet = snippet.substring(0, snippet.length -2);
        }
        snippet += '], ';

        //Build links
        snippet += '"links": [';
        if(this._links.length > 0){
            for (var i = 0; i < this._links.length; i++) {;
                curLink = this._links[i];
                snippet += '"' + curLink + '", ';            
            };
            snippet = snippet.substring(0, snippet.length -2);
        }
        
        snippet += ']';
        snippet += "}";
        return snippet;
    }    
};

var SnippetHelper = {
    init: function(){
        return this;
    },

    encodeString: function (str){
        var regxPatt, 
            encodedString;
        //Convert line breaks to <br />                
        regxPatt = /\n/g;                            
        encodedString = str.replace(regxPatt, "<br/>");
        //Escape characters
        encodedString = escape(str);
        //the escape function misses out '+', convert them.
        regxPatt = /\+/g;
        encodedString = encodedString.replace(regxPatt, '%2B');
        return encodedString;
    }
}


var EditorHelper = {    

    _editors: [],

    init: function () {
        this.editors = [];
        return this;
    },

    addNewFile: function(){
        var newHTML, 
            editorID, 
            editor, 
            TextMode;

        newHTML = '<div class="snippet-file"><input type="hidden" class="editornumber" value="' + this._editors.length + '"/><input type="text" class="span3 file-name" placeholder="readme.txt"><div class="aceeditor" id="editor' + this._editors.length + '"></div></div>';
        jQuery("#snippet-list").append(newHTML);

        editorID = "editor" + this._editors.length;
        console.log(editorID);
        editor = ace.edit(editorID);
        editor.setTheme("ace/theme/solarized_dark");

        TextMode = require("ace/mode/text").Mode;
        editor.getSession().setMode(new TextMode());          
        this._editors.push(editor);
        
        snippetUI.registerFileNameListeners();        
        return false; 
    },

    getEditors: function () {
        return this._editors;
    },

    updateEditorList: function (newEditorList){
        this._editors = newEditorList;
    },

    setupEditors: function () {
        var editorID, 
            editor, 
            filename, 
            filetype, 
            editorNumber; 
            
        jQuery(".aceeditor").each(function () {
            editorID = jQuery(this).attr("id");            
            editor = ace.edit(editorID);
            editor.setTheme("ace/theme/solarized_dark");
            var tmpEditors = editorHelper.getEditors();
            tmpEditors.push(editor);
            editorHelper.updateEditorList(tmpEditors);

            filename = jQuery(this).siblings(".file-name").val();
            
            if(filename.indexOf(".") != -1){
                fileType = filename.substring(filename.lastIndexOf("."));
                editorNumber = jQuery(this).siblings(".editornumber").val();
                editorHelper.updateSyntaxHighlighting(fileType, editorNumber);
            }            
        });
    },

    updateSyntaxHighlighting: function (fileType, editorNumber){
        var editor;

        editor = this._editors[parseInt(editorNumber)];

        switch(fileType){            
            case ".css":
                var CSSMode = require("ace/mode/css").Mode;
                editor.getSession().setMode(new CSSMode());
            break;
            case ".html":
                var HTMLMode = require("ace/mode/html").Mode;
                editor.getSession().setMode(new HTMLMode());
            break;
            case ".htm":
                var HTMLMode = require("ace/mode/html").Mode;
                editor.getSession().setMode(new HTMLMode());
            break;
            case ".js":
                var JavaScriptMode = require("ace/mode/javascript").Mode;
                editor.getSession().setMode(new JavaScriptMode());  
            break;
            case ".json":
                var JSONMode = require("ace/mode/json").Mode;
                editor.getSession().setMode(new JSONMode());  
            break;                         
            case ".php":
                var PHPMode = require("ace/mode/php").Mode;
                editor.getSession().setMode(new PHPMode());  
            break;        
            case ".htaccess":
                var ScriptMode = require("ace/mode/sh").Mode;
                editor.getSession().setMode(new ScriptMode());  
            break;
            case ".sh":
                var ScriptMode = require("ace/mode/sh").Mode;
                editor.getSession().setMode(new ScriptMode());  
            break;
            case ".sql":
                var SQLMode = require("ace/mode/sql").Mode;
                editor.getSession().setMode(new SQLMode());
            break;
            case ".xml":
                var XMLMode = require("ace/mode/xml").Mode;
                editor.getSession().setMode(new XMLMode());
            break;
        }
    }
};

var SnippetUI = {
    init: function(){
        return this;
    },

    addLink: function(newLink){
        var regex, 
            match, 
            linkHTML;

        if(newLink != ""){
            regex = /https?:\/\//;                            
            match = regex.exec(newLink);  
            if(!match){                        
                newLink = "http://" + newLink;
            }

            codeSnippet.addLink(newLink);

            linkHTML = '<li><a class="link" href="' + newLink + '" target="blank" title="Opens in new tab">' + newLink + '</a> <a href="#" class="delete-link">(delete)</a></li>';
            jQuery("#links").append(linkHTML);
            jQuery("#link").val('');
        }

        this.registerDeleteLinkListeners();
    },

    registerAllListeners: function(){
        this.registerDeleteLinkListeners();
        this.registerFileNameListeners();
        this.registerNewLinkListeners();
        this.registerMiscEventListeners();
    },
    
    registerDeleteLinkListeners: function () {
        jQuery("a.delete-link").click(function(event) {
            event.preventDefault();
            codeSnippet.removeLink(jQuery(this).siblings("a").text());
            jQuery(this).parents("li").remove();
        });    
    },

    registerFileNameListeners: function () {
        var filename, fileType, editorNumber;

        jQuery(".file-name").each(function () {
            jQuery(this).blur(function () {
                
                filename = jQuery(this).val();
                
                if(filename.indexOf(".") != -1){
                    fileType = filename.substring(filename.lastIndexOf("."));
                    editorNumber = jQuery(this).siblings(".editornumber").val();
                    editorHelper.updateSyntaxHighlighting(fileType, editorNumber);
                }
            });
        });
    },

    registerNewLinkListeners: function(){
        jQuery("#add-new-link").click(function(event){
            event.preventDefault();
            snippetUI.addLink(jQuery('#link').val().trim());
        });

        jQuery('#link').keydown(function(event) {
            if (event.which === 13) {
                event.preventDefault();            
                snippetUI.addLink(jQuery(this).val().trim());
            }       
        });
    },

    registerMiscEventListeners: function(){
        jQuery("#add-new-file").click(function(event){
            event.preventDefault();
            editorHelper.addNewFile();
        });
        
        jQuery("#save").click(function(event){
            event.preventDefault();        
            snippetUI.initiateSave();
        });

        jQuery("#delete").click(function(event) {
            event.preventDefault();
            if(confirm("Sure?")){
                jQuery.ajax({
                    url: "api/snippet/" + jQuery("#delete-id").val(),
                    cache: false,
                    type: "DELETE",
                    beforeSend: function(){
                        jQuery('#loading').modal({keyboard: false, backdrop: "static"});
                    },
                    complete: function(){
                      jQuery('#loading').modal('hide');
                    },
                    success: function(){
                        jQuery("#success p").text("Snippet deleted"); 
                        jQuery("#success").show();
                        jQuery("#main-container").fadeOut('fast').remove();

                    },
                    error: function(){
                        jQuery("#error p").text("Sorry, could not delete the snippet.");
                        jQuery("#error").fadeIn('fast');
                    }
                });
            }
        });
    },

    initiateSave: function(){
        var snippetJSON, 
            files, 
            editors, 
            curEditor, 
            tmpFileName, 
            fileContents,
            formHTML;

        //Set the snippet title, description and meta data
        codeSnippet.setTitle(jQuery("#title").val());
        codeSnippet.updateKeywords(jQuery("#title").val().split(" "));
        codeSnippet.setDescription(jQuery("#description").val());
        codeSnippet.setViews(jQuery("#views").val());
        codeSnippet.setDateCreated(jQuery("#dateCreated").val());
        codeSnippet.setLastModified(jQuery("#lastModified").val());

        //add all the files to the snippet          
        files = [];

        jQuery(".snippet-file").each(function () {
            tmpFileName = jQuery(this).children('.file-name').val().trim();                
            if(tmpFileName !== ""){
                editors = editorHelper.getEditors();
                curEditor = editors[parseInt(jQuery(this).children('.editornumber').val())];
                fileContents = snippetHelper.encodeString(curEditor.getSession().getValue());
                file = {
                    filename: tmpFileName,
                    filecontent: fileContents
                };
                files.push(file);
            }
        });

        codeSnippet.setFiles(files);
        
        snippetJSON = codeSnippet.toJSONString();
            
        if(jQuery("#title").val().trim() != ""){            
            jQuery("#snippet").val(snippetJSON);

            if(jQuery("#snippet").attr("name") == "update-snippet"){
                jQuery.ajax({
                    url: "api/snippet/" + jQuery("#update-id").val(),
                    cache: false,
                    type: "PUT",
                    data: jQuery("#snippet").val(),
                    beforeSend: function(){
                        jQuery('#loading').modal({keyboard: false, backdrop: "static"});
                    },
                    complete: function(){
                      jQuery('#loading').modal('hide');
                    },
                    success: function(data){
                        jQuery("#success p").text("Snippet updated");
                        jQuery("#success").fadeIn('fast');
                    },
                    error: function(data){
                        jQuery("#error p").text("Sorry, could not update the snippet.");
                        jQuery("#error").fadeIn('fast');
                    }
                });
            }else if(jQuery("#snippet").attr("name") == "new-snippet"){
                jQuery.ajax({
                    url: "api/snippet/1",
                    cache: false,
                    type: "POST",
                    data: jQuery("#snippet").val(),
                    beforeSend: function(){
                        jQuery('#loading').modal({keyboard: false, backdrop: "static"});
                    },
                    complete: function(){
                        jQuery('#loading').modal('hide');
                    },
                    success: function(data){
                        var snippet = JSON.parse(data);                        
                        jQuery("#success p").text("Snippet added!");
                        jQuery("#success").fadeIn('fast');
                        
                        //update the new button to read "update"
                        jQuery("#update-buttons a:first").html('<i class="icon-refresh icon-white"></i>&nbsp;Update');

                        //Add in the delete button
                        jQuery("#update-buttons").append('<a href="#" id="delete" class="btn btn-danger"><i class="icon-trash icon-white"></i>&nbsp;Delete</a>');

                        //add h0idden forms
                        snippetUI.setupHiddenForms(snippet.id);
                    },
                    error: function(data){
                        jQuery("#error p").text("Sorry, could not add the snippet.");
                        jQuery("#error").fadeIn('fast');
                    }
                });
            }           
        }else{
            jQuery("#validation-error").show();
            return false;
        }
    },

    setupHiddenForms: function(id){
        //add extra field to hidden form 
        formHTML =  '<form action="api/snippets" method="put" id="snippet-form">';
        formHTML +=     '<input type="hidden" id="update-id" name="id" value="' + id + '"/>';
        formHTML +=     '<input type="hidden" id="snippet" name="update-snippet"/>';
        formHTML += '</form>';
        formHTML += '<form action="api/snippets" method="delete" id="delete-form">';
        formHTML +=     '<input type="hidden" id="delete-id" name="id" value="' + id + '"/>';
        formHTML +=     '<input type="hidden" id="snippet" name="delete-snippet"/>';
        formHTML += '</form>';
        jQuery("#hidden-forms").html(formHTML);
        registerAllListeners();
    }
}

jQuery(document).ready(function () {
    //setup our objects
    snippetUI = SnippetUI.init();
    editorHelper = EditorHelper.init();
    snippetHelper = SnippetHelper.init();
    codeSnippet = CodeSnippet.init();

    //run through and setup all the editors
	editorHelper.setupEditors();

    //Register all ui event listeners
    snippetUI.registerAllListeners();

    
});