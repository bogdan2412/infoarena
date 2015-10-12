# Vagrantfile API/syntax version. Don't touch unless you know what you're doing!
VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
  config.vm.box = "ubuntu/trusty64"

  config.vm.hostname = "infoarena2.localhost"

  config.vm.provision :shell, :path => "scripts/vagrant_provision.sh"

  config.vm.define :Infoarena do |t|

  end

  config.vm.network "private_network", ip: "20.0.0.201"

  config.ssh.forward_agent = true

  config.vm.synced_folder ".", "/vagrant", disabled: true
  config.vm.synced_folder ".", "/var/infoarena/repo"
end
