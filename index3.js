// Attendre que le DOM soit complètement chargé
document.addEventListener('DOMContentLoaded', function () {

	// SIDEBAR DROPDOWN
	const allDropdown = document.querySelectorAll('#sidebar .side-dropdown');
	const sidebar = document.getElementById('sidebar');

	if (sidebar) {
		allDropdown.forEach(item => {
			const a = item.parentElement.querySelector('a:first-child');
			if (a) {
				a.addEventListener('click', function (e) {
					e.preventDefault();

					if (!this.classList.contains('active')) {
						allDropdown.forEach(i => {
							const aLink = i.parentElement.querySelector('a:first-child');

							aLink.classList.remove('active');
							i.classList.remove('show');
						})
					}

					this.classList.toggle('active');
					item.classList.toggle('show');
				})
			}
		})
	}




	// SIDEBAR COLLAPSE
	const toggleSidebar = document.querySelector('nav .toggle-sidebar');
	const allSideDivider = document.querySelectorAll('#sidebar .divider');

	if (sidebar && toggleSidebar) {
		if (sidebar.classList.contains('hide')) {
			allSideDivider.forEach(item => {
				item.textContent = '-'
			})
			allDropdown.forEach(item => {
				const a = item.parentElement.querySelector('a:first-child');
				a.classList.remove('active');
				item.classList.remove('show');
			})
		} else {
			allSideDivider.forEach(item => {
				item.textContent = item.dataset.text;
			})
		}

		toggleSidebar.addEventListener('click', function () {
			sidebar.classList.toggle('hide');

			if (sidebar.classList.contains('hide')) {
				allSideDivider.forEach(item => {
					item.textContent = '-'
				})

				allDropdown.forEach(item => {
					const a = item.parentElement.querySelector('a:first-child');
					a.classList.remove('active');
					item.classList.remove('show');
				})
			} else {
				allSideDivider.forEach(item => {
					item.textContent = item.dataset.text;
				})
			}
		})




		sidebar.addEventListener('mouseleave', function () {
			if (this.classList.contains('hide')) {
				allDropdown.forEach(item => {
					const a = item.parentElement.querySelector('a:first-child');
					a.classList.remove('active');
					item.classList.remove('show');
				})
				allSideDivider.forEach(item => {
					item.textContent = '-'
				})
			}
		})



		sidebar.addEventListener('mouseenter', function () {
			if (this.classList.contains('hide')) {
				allDropdown.forEach(item => {
					const a = item.parentElement.querySelector('a:first-child');
					a.classList.remove('active');
					item.classList.remove('show');
				})
				allSideDivider.forEach(item => {
					item.textContent = item.dataset.text;
				})
			}
		})
	}



	// PROFILE DROPDOWN
	const profile = document.querySelector('nav .profile');
	if (profile) {
		const imgProfile = profile.querySelector('img');
		const dropdownProfile = profile.querySelector('.profile-link');

		if (imgProfile && dropdownProfile) {
			imgProfile.addEventListener('click', function () {
				dropdownProfile.classList.toggle('show');
			})

			window.addEventListener('click', function (e) {
				if (e.target !== imgProfile) {
					if (e.target !== dropdownProfile) {
						if (dropdownProfile.classList.contains('show')) {
							dropdownProfile.classList.remove('show');
						}
					}
				}
			})
		}
	}



	// MENU
	const allMenu = document.querySelectorAll('main .content-data .head .menu');

	allMenu.forEach(item => {
		const icon = item.querySelector('.icon');
		const menuLink = item.querySelector('.menu-link');

		if (icon && menuLink) {
			icon.addEventListener('click', function () {
				menuLink.classList.toggle('show');
			})
		}
	})



	window.addEventListener('click', function (e) {
		allMenu.forEach(item => {
			const icon = item.querySelector('.icon');
			const menuLink = item.querySelector('.menu-link');

			if (icon && menuLink) {
				if (e.target !== icon) {
					if (e.target !== menuLink) {
						if (menuLink.classList.contains('show')) {
							menuLink.classList.remove('show')
						}
					}
				}
			}
		})
	})





	// PROGRESSBAR
	const allProgress = document.querySelectorAll('main .card .progress');

	allProgress.forEach(item => {
		item.style.setProperty('--value', item.dataset.value)
	})






	// APEXCHART - Seulement si l'élément existe
	const chartElement = document.querySelector("#chart");
	if (chartElement) {
		// Check if serverData exists
		if (window.serverData) {
			const series1Data = window.serverData.series1.map(item => item.y);
			const series2Data = window.serverData.series2.map(item => item.y);
			const categories = window.serverData.series1.map(item => item.label);

			var options = {
				series: [{
					name: 'Taille (MB)',
					data: series1Data
				}, {
					name: 'Nombre de Tables',
					data: series2Data
				}],
				chart: {
					height: 350,
					type: 'bar',
					zoom: {
						enabled: false
					}
				},
				dataLabels: {
					enabled: false
				},
				stroke: {
					curve: 'smooth',
					width: 2
				},
				xaxis: {
					categories: categories,
				},
				tooltip: {
					y: {
						formatter: function (val) {
							return val
						}
					}
				},
				colors: ['#3C91E6', '#DB504A'] // Use theme colors if possible, blue and red/orange
			};

			var chart = new ApexCharts(chartElement, options);
			chart.render();
		}


	}

}); // Fin du DOMContentLoaded