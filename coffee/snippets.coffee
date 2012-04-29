#some global vars
editorCount = 0
editors = []

getSnippetLinks = () ->
    numLinks = jQuery("ul#links>li").size()
    links = "["
    if numLinks > 0       
        jQuery("ul#links>li>a.link").each ->
            links = "#{links} #{encodeString jQuery(this).text()}"        
        #remove last comma and space characters
        links = links.substring 0,links.length-2
    links = "#{links}]"
    return links

encodeString = (str) ->
    #Convert line breaks to <br />      
    patt = /\n/g
    encodedString = str.replace patt,"<br/>"    
    #Escape characters
    encodedString = escape str     
    #the escape function misses out '+' so manually convert them.
    patt2 = /\+/g
    encodedString = encodedString.replace patt2,'%2B'
    encodedString

getSnippetFiles = () ->
    #Build up files.
    files = "["
    jQuery(".snippet-file").each -> 
        curEditor = editors[parseInt jQuery(this).children('.editornumber').val() ]
        fileContents = encodeString curEditor.getSession().getValue()
        filename = encodeString jQuery(this).children(".file-name").val()
        files = "#{files} {\"filename\": \"#{filename}\", \"filecontent\": \"#{fileContents}\"}, "
    #remove last comma and space characters
    files = files.substring 0,files.length-2
    files = "#{files}]"

updateSyntaxHighlighting = (fileType, editorNumber) ->
    editor = editors[parseInt editorNumber]
    switch fileType        
        when ".c", ".cpp" 
            CMode = require("ace/mode/c_cpp").Mode
            editor.getSession().setMode(new CMode())
        when ".coffee" 
            CoffeeMode = require("ace/mode/coffee").Mode
            editor.getSession().setMode(new CoffeeMode())
        when ".cs" 
            CSMode = require("ace/mode/csharp").Mode
            editor.getSession().setMode(new CSMode())
        when ".css" 
            CSSMode = require("ace/mode/css").Mode
            editor.getSession().setMode(new CSSMode())        
        when ".html", ".htm" 
            HTMLMode = require("ace/mode/html").Mode
            editor.getSession().setMode(new HTMLMode())
        when ".java" 
            JavaMode = require("ace/mode/java").Mode
            editor.getSession().setMode(new JavaMode()) 
        when ".js" 
            JavaScriptMode = require("ace/mode/javascript").Mode
            editor.getSession().setMode(new JavaScriptMode()) 
        when ".json" 
            JSONMode = require("ace/mode/json").Mode
            editor.getSession().setMode(new JSONMode())
        when ".less" 
            LessMode = require("ace/mode/less").Mode
            editor.getSession().setMode(new LessMode())
        when ".pl" 
            PerlMode = require("ace/mode/perl").Mode
            editor.getSession().setMode(new PerlMode())  
        when ".php" 
            PHPMode = require("ace/mode/php").Mode
            editor.getSession().setMode(new PHPMode())  
        when ".py" 
            PythonMode = require("ace/mode/python").Mode
            editor.getSession().setMode(new PythonMode())  
        when ".rb" 
            RubyMode = require("ace/mode/ruby").Mode
            editor.getSession().setMode(new RubyMode())  
        when ".scss" 
            SCSSMode = require("ace/mode/scss").Mode
            editor.getSession().setMode(new SCSSMode())  
        when ".htaccess", ".sh" 
            ScriptMode = require("ace/mode/sh").Mode
            editor.getSession().setMode(new ScriptMode()) 
        when ".psgl",".sql" 
            SQLMode = require("ace/mode/sql").Mode
            editor.getSession().setMode(new SQLMode())
        when ".xml" 
            XMLMode = require("ace/mode/xml").Mode
            editor.getSession().setMode(new XMLMode())

registerDeleteLinkListeners = () -> 
    jQuery("a.delete-link").click ->
        jQuery(this).parents("li").remove()
        false

registerFileNameListeners = () ->
    jQuery(".file-name").each ->
        jQuery(this).blur -> 
            filename = jQuery(this).val()
            if filename.indexOf(".") isnt -1
                fileType = filename.substring(filename.lastIndexOf("."))
                editorNumber = jQuery(this).siblings(".editornumber").val()
                updateSyntaxHighlighting fileType, editorNumber

saveSnippet = () ->
    if jQuery("#title").val().trim() isnt ""
        title = encodeString(jQuery("#title").val())
        description = encodeString(jQuery("#description").val())
        #snippet object properties
        links = getSnippetLinks()
        files = getSnippetFiles()
        views = jQuery("#views").val()
        dateCreated = jQuery("#dateCreated").val()
        lastModified = jQuery("#lastModified").val()
        #build JSON for snippet and post it
        snippet = "{\"title\": \"#{title}\", \"description\": \"#{description}\", \"files\": #{files}, \"links\": #{links}, \"date_created\": \"#{dateCreated}\", \"last_modified\": \"#{lastModified}\", \"views\": #{views}}"
        jQuery("#snippet").val(snippet)
        jQuery("#snippet-form").submit()
    else
        jQuery("#validation-error").show()    
    false

addLink = () ->
    newLink = jQuery("#link").val().trim()    
    if(newLink != "")
        regex = /https?:\/\//
        match = regex.exec(newLink)
        if not match then newLink = "http://" + newLink        
        linkHTML = '<li><a class="link" href="' + newLink + '" target="blank" title="Opens in new tab">' + newLink + '</a> <a href="#" class="delete-link">(delete)</a></li>'
        jQuery("#links").append(linkHTML)
        jQuery("#link").val('')
    registerDeleteLinkListeners()

setupEditors = () ->
    jQuery(".aceeditor").each ->
        editorID = jQuery(this).attr("id")
        editor = ace.edit(editorID)
        editor.setTheme("ace/theme/solarized_dark")
        editors.push(editor)
        filename = jQuery(this).siblings(".file-name").val()
        
        if filename.indexOf(".") isnt -1
            fileType = filename.substring(filename.lastIndexOf("."))
            editorNumber = jQuery(this).siblings(".editornumber").val()
            updateSyntaxHighlighting(fileType, editorNumber)        
        editorCount++;

jQuery(document).ready ->
    setupEditors()
    #set up new file click listener
    jQuery("#add-new-file").click -> 
        newHTML = '<div class="snippet-file"><input type="hidden" class="editornumber" value="' + editorCount + '"/><input type="text" class="span3 file-name" placeholder="readme.txt"><div class="aceeditor" id="editor' + editorCount + '"></div></div>';
        jQuery("#snippet-list").append(newHTML)
        #setup editor for the new file with default theme and plain text mode
        editorID = "editor" + editorCount        
        editor = ace.edit(editorID)
        editor.setTheme("ace/theme/solarized_dark")
        TextMode = require("ace/mode/text").Mode
        editor.getSession().setMode(new TextMode())
        editors.push(editor)
        registerFileNameListeners()
        editorCount++
        false 
    #setup new link click listener and enter key binding
    jQuery("#add-new-link").click(addLink)
    jQuery('#link').keydown (event) ->
        if event.which is 13
            event.preventDefault()
            addLink()
    #setup save click event
    jQuery("#save").click(saveSnippet)
    #setup delete snippet click event
    jQuery("#delete").click ->
        if(confirm("Sure?"))
            jQuery("#delete-form").submit()
    #register some click listners for deleting links and watching file name changes
    registerDeleteLinkListeners()
    registerFileNameListeners()