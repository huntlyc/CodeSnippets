//Some Globals, cause we all love 'em so much
var editorCount, editorHelper, codeSnippet, snippetUI;

var CodeSnippet = {
    _title: "",
    _description: "",
    _links: [],
    _files: [],
    _views: 1,
    _dateCreated: new Date(),
    _lastModified: new Date(),

    init: function () {
        return this;
    },    

    getSnippetFiles: function () {
        //Build up files.
        var files = "[";                
        jQuery(".snippet-file").each(function () {
            var editors = editorHelper.getEditors();
            var curEditor = editors[parseInt(jQuery(this).children('.editornumber').val())];
            var fileContents = snippetHelper.encodeString(curEditor.getSession().getValue());
            files = files + '{"filename": "' + snippetHelper.encodeString(jQuery(this).children('.file-name').val()) + '", ';

            files = files + '"filecontent": "' + fileContents + '"}, ';
        });
        //remove last comma and space characters
        files = files.substring(0,files.length -2);
        files = files + "]";

        return files;
    },

    saveSnippet: function () {
        if(jQuery("#title").val().trim() != ""){
            this._title = snippetHelper.encodeString(jQuery("#title").val());
            this._description = snippetHelper.encodeString(jQuery("#description").val());

            //build links
            this._links = this.getSnippetLinks();
            this._files = this.getSnippetFiles();

            this._views = jQuery("#views").val();
            this._dateCreated = jQuery("#dateCreated").val();
            this._lastModified = jQuery("#lastModified").val();

            //var snippet = '{"title": "' + this.title + '", "description": "' + this.description + '", "files": ' + this.files + ', "links": ' + this.links + ', "date_created": "' + this.dateCreated + '", "last_modified": "' + this.lastModified + '", "views": ' + this.views + '}';
            //jQuery("#snippet").val(snippet);
            console.log(this);
            //jQuery("#snippet-form").submit();
        }else{
            jQuery("#validation-error").show();
        }
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
    }
};

var SnippetHelper = {
    init: function(){
        return this;
    },

    encodeString: function (str){
        //Convert line breaks to <br />                
        var patt = /\n/g;                            
        var encodedString = str.replace(patt, "<br/>");
        //Escape characters
        encodedString = escape(str);
        //the escape function misses out '+', convert them.
        var patt2 = /\+/g;
        encodedString = encodedString.replace(patt2, '%2B');
        return encodedString;
    }
}


var EditorHelper = {    

    editors: [],

    init: function () {
        this.editors = [];
        return this;
    },

    addNewFile: function(){
        var newHTML = '<div class="snippet-file"><input type="hidden" class="editornumber" value="' + editorCount + '"/><input type="text" class="span3 file-name" placeholder="readme.txt"><div class="aceeditor" id="editor' + editorCount + '"></div></div>';
        jQuery("#snippet-list").append(newHTML);

        var editorID = "editor" + this._editors.length;
        console.log(editorID);
        var editor = ace.edit(editorID);
        editor.setTheme("ace/theme/solarized_dark");

        var TextMode = require("ace/mode/text").Mode;
        editor.getSession().setMode(new TextMode());  
        var editors = editorHelper.getEditors();
        editors.push(editor);
        editorHelper.updateEditorList(editors);
        snippetUI.registerFileNameListeners();
        editorCount++;
        return false; 
    },

    getEditors: function () {
        return this.editors;
    },

    updateEditorList: function (newEditorList){
        this.editors = newEditorList;
    },

    setupEditors: function () {
        var editors, editorID, editor,filename, filetype, editorNumber; 
        jQuery(".aceeditor").each(function () {
            editorID = jQuery(this).attr("id");            
            editor = ace.edit(editorID);
            editor.setTheme("ace/theme/solarized_dark");
            editors = EditorHelper.getEditors();
            editors.push(editor);
            editorHelper.updateEditorList(editors);

            filename = jQuery(this).siblings(".file-name").val();
            
            if(filename.indexOf(".") != -1){
                fileType = filename.substring(filename.lastIndexOf("."));
                editorNumber = jQuery(this).siblings(".editornumber").val();
                editorHelper.updateSyntaxHighlighting(fileType, editorNumber);
            }
            editorCount++;
        });
    },

    updateSyntaxHighlighting: function (fileType, editorNumber){
        console.log(fileType + " e: " + editorNumber);

        var editor = this.editors[parseInt(editorNumber)];

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

        console.log(newLink);
        if(newLink != ""){
            var regex = /https?:\/\//;                            
            var match = regex.exec(newLink);  
            if(!match){                        
                newLink = "http://" + newLink;
            }

            codeSnippet.addLink(newLink);

            var linkHTML = '<li><a class="link" href="' + newLink + '" target="blank" title="Opens in new tab">' + newLink + '</a> <a href="#" class="delete-link">(delete)</a></li>';
            jQuery("#links").append(linkHTML);
            jQuery("#link").val('');
        }

        this.registerDeleteLinkListeners();
    },
    
    registerDeleteLinkListeners: function () {
        jQuery("a.delete-link").click(function () {
            codeSnippet.removeLink(jQuery(this).siblings("a").text());
            jQuery(this).parents("li").remove();
            return false;
        });    
    },

    registerFileNameListeners: function () {
        jQuery(".file-name").each(function () {
            jQuery(this).blur(function () {
                var filename = jQuery(this).val();
                if(filename.indexOf(".") != -1){
                    var fileType = filename.substring(filename.lastIndexOf("."));
                    var editorNumber = jQuery(this).siblings(".editornumber").val();
                    editorHelper.updateSyntaxHighlighting(fileType, editorNumber);
                }
            });
        });
    },

    registerNewLinkListeners: function(){
        jQuery("#add-new-link").click(function(){        
            snippetUI.addLink(jQuery('#link').val().trim());
        });

        jQuery('#link').keydown(function (event) {
            if (event.which === 13) {
                event.preventDefault();            
                snippetUI.addLink(jQuery(this).val().trim());
            }       
        });
    },

    registerMiscEventListeners: function(){
        jQuery("#add-new-file").click(function () {                    
            editorHelper.addNewFile();
        });
        

        jQuery("#save").click(function(){
            codeSnippet.saveSnippet();
        });

        jQuery("#delete").click(function () {
            if(confirm("Sure?")){
                jQuery("#delete-form").submit();
            }
        });
    }
}


jQuery(document).ready(function () {
    editorCount = 0;
    snippetUI = SnippetUI.init();
    editorHelper = EditorHelper.init();
    snippetHelper = SnippetHelper.init();
    codeSnippet = CodeSnippet.init();

	editorHelper.setupEditors();

    snippetUI.registerNewLinkListeners();
    snippetUI.registerDeleteLinkListeners();
    snippetUI.registerFileNameListeners();
    snippetUI.registerMiscEventListeners();

});