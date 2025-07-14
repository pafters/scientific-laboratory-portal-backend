<?php

declare(strict_types=1);

namespace Phosagro\System\Iblock;

final class WebFormProperty
{
    private int $number = 0;

    public function getPropertyFieldHtml(
        array $property,
        array $value,
        array $control,
    ): string {
        $id = sprintf('phosagro_web_form_%d', ++$this->number);
        $value = $value['VALUE'] ?? '';
        $name = '';

        if ('' !== $value) {
            $form = \CForm::GetByID($value)->Fetch();
            $form = (\is_array($form) ? $form : []);
            $value = sprintf('%d', $form['ID']);
            $name = (string) $form['NAME'];
        }

        return sprintf(
            <<<'HTML'
            <div data-id="%3$s" data-name="%4$s" id="%1$s_container" style="display:flex;gap:5px;">
                <label for="%1$s_id"></label>
                <input id="%1$s_id" name="%2$s" readonly style="width:50px;" type="text" value="%3$s">
                <label for="%1$s_search"></label>
                <input id="%1$s_search" placeholder="Введите слова для поиска" style="width:200px;" type="text">
            </div>
            <script>
                (function () {
                    const container = document.getElementById("%1$s_container");
                    const id = document.getElementById("%1$s_id");
                    const search = document.getElementById("%1$s_search");

                    var timeout = null;

                    function doSearch() {
                        if (search.value) {
                            fetch('/api/system/find-web-form/' + encodeURIComponent(search.value) + '/')
                                .then(function (response) {
                                    return response.json();
                                })
                                .then(function (response) {
                                    showOptions(response.data.forms);
                                })
                            ;
                        }
                    }

                    function onSearch() {
                        if (timeout !== null) {
                            clearTimeout(timeout);
                        }
                        timeout = setTimeout(doSearch, 500);
                    }

                    function findOptions() {
                        return container.querySelectorAll('input[type="button"]');
                    }

                    function showOptions(forms) {
                        var option, active = null;
                        for (option of findOptions()) {
                            if (option.dataset.id != id.value) {
                                container.removeChild(option);
                            } else {
                                active = id.value;
                            }
                        }
                        for (index = 0, total = forms.length; index < total; ++index) {
                            if (forms[index].id != active) {
                                option = document.createElement('input');
                                option.dataset.id = forms[index].id;
                                option.style.margin = '0';
                                option.style.height = '27px';
                                option.type = 'button';
                                option.value = ((forms[index].id == id.value) ? '☑' : '☐') + ' ' + forms[index].name;
                                option.addEventListener('click', selectOption);
                                container.appendChild(option);
                            }
                        }
                    }

                    function selectOption(event) {
                        var option;
                        for (option of findOptions()) {
                            option.value = '☐' + option.value.substring(1);
                        }
                        option = event.currentTarget;
                        option.value = '☑' + option.value.substring(1)
                        id.value = option.dataset.id;
                    }

                    search.addEventListener('change', onSearch);
                    search.addEventListener('keyup', onSearch);
                    search.addEventListener('paste', onSearch);

                    if (container.dataset.id) {
                        showOptions([{
                            'id': container.dataset.id,
                            'name': container.dataset.name,
                        }]);
                    }
                })();
            </script>
            HTML,
            $id,
            $control['VALUE'] ?? '',
            htmlspecialchars($value),
            htmlspecialchars($name),
        );
    }
}
