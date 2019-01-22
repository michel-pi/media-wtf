$(document).ready(function() {
    var inputLink = document.getElementById("inputLink");

    if (!inputLink)
    {

    }
    else
    {
        inputLink.focus();
        inputLink.select();
    }
});
function clipboard(element)
{
    element.focus();
    element.select();
    document.execCommand("copy");
}
function showTooltip(element)
{
    $("#" + element.id).tooltip("show");
}
function hideTooltip(element)
{
    $("#" + element.id).tooltip("hide");
}
function copyTextNavigator(text)
{
    navigator.clipboard.writeText(text);
}
function copyTextDocument(text)
{
    var child = document.createElement("textarea");
    child.value = text;
    document.body.append(child);
    child.focus();
    child.Select();
    document.execCommand("copy");
    document.body.removeChild(child);
}