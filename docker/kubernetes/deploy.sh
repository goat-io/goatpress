## Azure kubernetes deployment
source ./kubernetes/common/index.sh

step "Checking if Azure CLI is installed"

command -v az >/dev/null 2>&1 || { 
    echo >&2 "$RED ERROR $NC - 'Azure CLI' is not installed on this host, please install it.  Aborting."; 
    exit 1; 
}

success "Azure CLI is installed"

if az account show | grep -q "tenantId"; then
   success "Logged in to azure"

    ## Set current context and Dashboard
    az aks get-credentials --resource-group DefaultResourceGroup-EUS --name {yourAPPNAME}

    echo "##########################"
    echo "# Current context: "
    kubectl config current-context
    echo "##########################"

    echo "If you want to open the Kubernetes Dashboard run:"
    echo "az aks browse --resource-group DefaultResourceGroup-EUS  --name {yourAPPNAME}"
   
   # az aks get-credentials --resource-group QA-SOCORP-{yourAPPNAME}-RG --name {yourAPPNAME}-kubernetes
   
   ## Open Kubernetes Dashboard
   ## az aks browse --resource-group QA-SOCORP-{yourAPPNAME}-RG --name {yourAPPNAME}-kubernetes
else
    error "You need to login to your Azure account";
    az login
    exit 1;
fi