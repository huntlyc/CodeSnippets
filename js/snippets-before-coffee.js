var editors = [];

function getSnippetLinks(){
    var links = "";
    var numLinks = jQuery("ul#links>li").size();
    links = "[";
    if(numLinks > 0){                    
        jQuery("ul#links>li>a.link").each(function(){
            links = links + '"' + encodeString(jQuery(this).text()) + '", ';
        });
        //remove last comma and space characters
        links = links.substring(0,links.length -2);                    
    }
    links = links + "]";
    return links;
}

function encodeString(str){
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

function getSnippetFiles(){
    //Build up files.
    var files = "[";                
    jQuery(".snippet-file").each(function(){
        var curEditor = editors[parseInt(jQuery(this).children('.editornumber').val())];
        var fileContents = encodeString(curEditor.getSession().getValue());
        files = files + '{"filename": "' + encodeString(jQuery(this).children('.file-name').val()) + '", ';

        files = files + '"filecontent": "' + fileContents + '"}, ';
    });
    //remove last comma and space characters
    files = files.substring(0,files.length -2);
    files = files + "]";

    return files;
}

function updateSyntaxHighlighting(fileType, editorNumber){
    console.log(fileType + " e: " + editorNumber);

    var editor = editors[parseInt(editorNumber)];

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

function registerDeleteLinkListeners(){
    jQuery("a.delete-link").click(function(){
        jQuery(this).parents("li").remove();
        return false;
    });    
}

function registerFileNameListeners(){
    jQuery(".file-name").each(function(){
        jQuery(this).blur(function(){
            var filename = jQuery(this).val();
            if(filename.indexOf(".") != -1){
                var fileType = filename.substring(filename.lastIndexOf("."));
                var editorNumber = jQuery(this).siblings(".editornumber").val();
                updateSyntaxHighlighting(fileType, editorNumber);
            }
        });
    });
}


function saveSnippet(){
    if(jQuery("#title").val().trim() != ""){
        var title = encodeString(jQuery("#title").val());
        var description = encodeString(jQuery("#description").val());

        //build links
        var links = getSnippetLinks();
        var files = getSnippetFiles();

        var views = jQuery("#views").val();
        var dateCreated = jQuery("#dateCreated").val();
        var lastModified = jQuery("#lastModified").val();

        var snippet = '{"title": "' + title + '", "description": "' + description + '", "files": ' + files + ', "links": ' + links + ', "date_created": "' + dateCreated + '", "last_modified": "' + lastModified + '", "views": ' + views + '}';
        jQuery("#snippet").val(snippet);
        jQuery("#snippet-form").submit();
    }else{
        jQuery("#validation-error").show();
    }
}

function addLink(){
    var newLink = jQuery("#link").val().trim();
    console.log(newLink);
    if(newLink != ""){

        var regex = /https?:\/\//;                            
        var match = regex.exec(newLink);  
        if(!match){                        
            newLink = "http://" + newLink;
        }
        var linkHTML = '<li><a class="link" href="' + newLink + '" target="blank" title="Opens in new tab">' + newLink + '</a> <a href="#" class="delete-link">(delete)</a></li>';
        jQuery("#links").append(linkHTML);
        jQuery("#link").val('');
    }

    registerDeleteLinkListeners();
}

function setupEditors(){
	jQuery(".aceeditor").each(function(){
		var editorID = jQuery(this).attr("id");
		console.log(editorID);
        var editor = ace.edit(editorID);
        editor.setTheme("ace/theme/solarized_dark");
        editors.push(editor);
        //var TextMode = require("ace/mode/text").Mode;
        //editor.getSession().setMode(new TextMode());
        var filename = jQuery(this).siblings(".file-name").val();
        if(filename.indexOf(".") != -1){
            var fileType = filename.substring(filename.lastIndexOf("."));
            var editorNumber = jQuery(this).siblings(".editornumber").val();
            updateSyntaxHighlighting(fileType, editorNumber);
        }
        editorCount++;
	});
}

var editorCount = 0;            
jQuery(document).ready(function(){
	editorCount;
	setupEditors();

    jQuery("#add-new-file").click(function(){                    
        var newHTML = '<div class="snippet-file"><input type="hidden" class="editornumber" value="' + editorCount + '"/><input type="text" class="span3 file-name" placeholder="readme.txt"><div class="aceeditor" id="editor' + editorCount + '"></div></div>';
        jQuery("#snippet-list").append(newHTML);

        var editorID = "editor" + editorCount;
        console.log(editorID);
        var editor = ace.edit(editorID);
        editor.setTheme("ace/theme/solarized_dark");

        var TextMode = require("ace/mode/text").Mode;
        editor.getSession().setMode(new TextMode());  
        editors.push(editor);
        registerFileNameListeners();
        editorCount++;
        return false; 
    });

    jQuery("#add-new-link").click(addLink);

    jQuery('#link').keydown(function(event) {
        if (event.which === 13) {
            event.preventDefault();
            addLink();
        }       
    });

    jQuery("#save").click(saveSnippet);

    jQuery("#delete").click(function(){
        if(confirm("Sure?")){
            jQuery("#delete-form").submit();
        }
    });

    registerDeleteLinkListeners();
    registerFileNameListeners();

});